<?php
declare(strict_types=1);

namespace App\Log\Engine;

use App\Model\Table\EventosSegurancaTable;
use App\Model\Table\UsersTable;
use Authentication\IdentityInterface;
use Cake\Http\ServerRequest;
use Cake\Log\Engine\BaseLog;
use Cake\Log\Log;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Routing\Router;
use Stringable;
use Throwable;

/**
 * Engine de log do escopo `seguranca` que grava cada evento na tabela `eventos_seguranca`, para a tela
 * "Eventos de segurança" (registrado no config/bootstrap.php, ao lado do arquivo `seguranca.log`).
 *
 * A conta afetada é a do usuário logado; no login (sem usuário), a dona do e-mail digitado, se ele
 * existir. O e-mail em si não é gravado. Uma falha aqui nunca interrompe a requisição: vira
 * `Log::error` (sem escopo, para não voltar a este engine).
 */
class EventosSegurancaLog extends BaseLog
{
    use LocatorAwareTrait;

    /**
     * @param mixed $level
     * @param \Stringable|string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public function log(mixed $level, Stringable|string $message, array $context = []): void
    {
        $evento = $context['evento'] ?? null;
        if (!is_string($evento) || $evento === '') {
            return;
        }

        try {
            $request = Router::getRequest();
            $this->fetchTable(EventosSegurancaTable::class)->registrar(
                $evento,
                (string)$level,
                $this->usuarioAfetado($request, $context),
                $request?->clientIp() ?: null,
            );
        } catch (Throwable $erro) {
            Log::error('Falha ao gravar o evento de segurança na tabela eventos_seguranca', [
                'evento' => $evento,
                'erro' => $erro->getMessage(),
            ]);
        }
    }

    /**
     * @param \Cake\Http\ServerRequest|null $request
     * @param array<string, mixed> $context
     * @return int|null
     */
    private function usuarioAfetado(?ServerRequest $request, array $context): ?int
    {
        $identidade = $request?->getAttribute('identity');
        if ($identidade instanceof IdentityInterface) {
            $id = $identidade->getIdentifier();

            return is_numeric($id) ? (int)$id : null;
        }

        $email = $context['email'] ?? null;
        if (!is_string($email) || $email === '') {
            return null;
        }

        $usuario = $this->fetchTable(UsersTable::class)
            ->find()
            ->select(['id'])
            ->where(['email' => strtolower($email)])
            ->first();

        return $usuario?->get('id');
    }
}
