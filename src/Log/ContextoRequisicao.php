<?php
declare(strict_types=1);

namespace App\Log;

use Cake\Http\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Extrai da requisição HTTP os dados que identificam "de onde veio" um log:
 * id da requisição, método, rota, IP e usuário autenticado.
 *
 * Usado pelo JsonFormatter (campo `requisicao` de toda linha de log) e pelo
 * ErrorLogger (exceções não tratadas).
 *
 * Por segurança, a URL crua NÃO é registrada quando a requisição casou com uma
 * rota — só `rota` (`Prefixo/Controller::action`). Caminhos podem carregar
 * segredo (ex.: `/users/redefinir-senha/{token}`) e o log vai pra um serviço
 * externo (ex.: um agregador de logs). O caminho (sem query string) só aparece quando não
 * há rota, ex.: 404 de rota inexistente.
 *
 * Implementação própria do core, que só depende de pacotes públicos.
 */
final class ContextoRequisicao
{
    private static ?string $id = null;

    /**
     * Id da requisição atual, o mesmo em todas as linhas de log dela — é o que
     * permite correlacionar, no Loki, logs diferentes de uma mesma requisição.
     *
     * Usa o UNIQUE_ID do Apache (mod_unique_id) quando disponível, que também
     * pode ir pro access log; senão gera um aleatório. O PHP não compartilha
     * estado entre requisições (PHP-FPM), então o static vale por requisição.
     */
    public static function id(): string
    {
        if (self::$id === null) {
            $uniqueId = $_SERVER['UNIQUE_ID'] ?? null;
            self::$id = is_string($uniqueId) && $uniqueId !== '' ? $uniqueId : bin2hex(random_bytes(8));
        }

        return self::$id;
    }

    /**
     * Descarta o id atual — para testes ou processos longos que tratam várias requisições.
     */
    public static function reiniciar(): void
    {
        self::$id = null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function extrair(?ServerRequestInterface $request): array
    {
        $dados = ['id' => self::id()];

        if ($request === null) {
            return $dados;
        }

        $dados['metodo'] = $request->getMethod();

        $rota = self::rota($request);
        if ($rota !== null) {
            $dados['rota'] = $rota;
        } else {
            $dados['url'] = $request->getUri()->getPath();
        }

        if ($request instanceof ServerRequest) {
            $dados['ip'] = $request->clientIp();
        }

        $identidade = $request->getAttribute('identity');
        $dados['usuario_id'] = is_object($identidade) && method_exists($identidade, 'getIdentifier')
            ? $identidade->getIdentifier()
            : null;

        return $dados;
    }

    /**
     * Só esquema + host do Referer — o caminho pode carregar segredo, como na URL
     * (ver docblock da classe). Suficiente pra saber se veio do próprio site.
     */
    public static function origemReferer(ServerRequestInterface $request): ?string
    {
        $referer = $request->getHeaderLine('Referer');
        $partes = $referer !== '' ? parse_url($referer) : false;

        if (!is_array($partes) || !isset($partes['scheme'], $partes['host'])) {
            return null;
        }

        $porta = isset($partes['port']) ? ':' . $partes['port'] : '';

        return $partes['scheme'] . '://' . $partes['host'] . $porta;
    }

    /**
     * `Prefixo/Controller::action` da rota casada; null quando a requisição não casou com nenhuma.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return string|null
     */
    private static function rota(ServerRequestInterface $request): ?string
    {
        $parametros = $request->getAttribute('params');
        if (!is_array($parametros) || empty($parametros['controller']) || empty($parametros['action'])) {
            return null;
        }

        $prefixo = !empty($parametros['prefix']) ? $parametros['prefix'] . '/' : '';

        return $prefixo . $parametros['controller'] . '::' . $parametros['action'];
    }
}
