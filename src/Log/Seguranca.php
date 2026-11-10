<?php
declare(strict_types=1);

namespace App\Log;

use Cake\Log\Log;

/**
 * Registro dos eventos de segurança (login errado, acesso negado, possível XSS) no escopo `seguranca`,
 * que o config/app.php manda para o `seguranca.log`.
 *
 * Substitui o antigo catálogo de eventos (GerenciadorEventos/Evento/EventosComplexos e a tabela `logs`).
 * Cada linha tem o nome do evento entre colchetes no começo da mensagem e também no contexto (`evento`),
 * para filtrar e alertar pelo campo, sem depender do texto. Os dados variáveis (e-mail digitado, campo)
 * vão no contexto, não na mensagem. IP, rota e usuário entram sozinhos no bloco `requisicao` do
 * JsonFormatter.
 *
 * O nível de cada evento é o mesmo do catálogo antigo: login errado é `notice`, acesso negado é
 * `warning`, XSS é `alert`.
 */
final class Seguranca
{
    public const ESCOPO = 'seguranca';

    public const LOGIN_CREDENCIAL_INVALIDA = 'login_credencial_invalida';
    public const LOGIN_2FA_INVALIDO = 'login_2fa_invalido';
    public const ACESSO_SOMENTE_ROOT = 'acesso_somente_root';
    public const XSS_DETECTADO = 'xss_detectado';

    /**
     * @param string $nivel Nível PSR-3 (`notice`, `warning`, `alert`...).
     * @param string $evento Uma das constantes desta classe.
     * @param string $descricao Texto fixo do evento (sem dado do usuário).
     * @param array<string, mixed> $dados Dados variáveis do evento.
     * @return void
     */
    public static function registrar(string $nivel, string $evento, string $descricao, array $dados = []): void
    {
        Log::write(
            $nivel,
            sprintf('[%s] %s', $evento, $descricao),
            ['scope' => self::ESCOPO, 'evento' => $evento] + $dados,
        );
    }
}
