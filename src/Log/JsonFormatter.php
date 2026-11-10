<?php
declare(strict_types=1);

namespace App\Log;

use Cake\Log\Formatter\AbstractFormatter;
use Cake\Routing\Router;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Formata qualquer log do CakePHP (manual ou automático, como exception não
 * tratada) como uma linha JSON, em vez do formato texto padrão.
 *
 * Uso: plugar via 'formatter' na config de qualquer engine de Cake\Log\Log.
 *
 * $context é serializado inteiro sob a chave 'contexto' — não usar 'contexto'
 * como chave dentro do array de contexto passado pra Log::error()/warning()/
 * etc., ou geraria aninhamento confuso ('contexto' => ['contexto' => ...]).
 *
 * Config opcional 'tenantEnvVar': nome de uma variável de ambiente (ex.: a
 * app pode setar via SetEnv no vhost) que identifica o tenant — se
 * configurada, todo log automaticamente ganha o campo 'tenant', sem precisar
 * passar isso em cada chamada de Log::error()/etc. Não tem valor default de
 * propósito — cada app tem sua própria convenção de nome de env var (ou
 * nenhuma), essa classe não assume qual.
 *
 * Config opcional 'ambienteEnvVar': mesma ideia, mas pro campo 'ambiente'.
 * Tem default 'APP_ENV' (não null) porque, diferente do tenant, APP_ENV é
 * é a convenção mais comum. Sem a variável, o campo
 * sai null. Passe null explicitamente pra desativar.
 *
 * Config opcional 'app': valor fixo (não nome de env var) pra identificar a
 * aplicação — não existe uma env var natural pra isso, então é só uma string
 * configurada uma vez em config/app.php (ex.: 'app' => 'keyanywhere'). Sem
 * default — se não configurado, o campo 'app' não aparece no log.
 *
 * Config opcional 'fuso' (default 'America/Campo_Grande'): fuso do campo 'timestamp',
 * fixado no formatter, e não no fuso padrão do PHP (que numa aplicação pode mudar por
 * tenant, como no KeyAnywhere). O 'timestamp' sai em RFC 3339 com milissegundos e o
 * deslocamento explícito (ex.: 2026-10-19T14:05:31.482-04:00), o formato que os agregadores
 * de log (ex.: Loki) usam como horário do evento.
 *
 * Config opcional 'requisicao' (default true): adiciona o campo 'requisicao'
 * com id da requisição, método, rota, IP e usuário autenticado, tirados de
 * Router::getRequest() — ver ContextoRequisicao. Fora de uma requisição HTTP
 * (CLI), só o id. Passe false pra desativar.
 */
class JsonFormatter extends AbstractFormatter
{
    protected array $_defaultConfig = [
        'app' => null,
        'ambienteEnvVar' => 'APP_ENV',
        'tenantEnvVar' => null,
        'requisicao' => true,
        'fuso' => 'America/Campo_Grande',
    ];

    /**
     * @param array<string, mixed> $context
     */
    public function format(mixed $level, string $message, array $context = []): string
    {
        // O Cake\Log\Log sempre injeta 'scope' no contexto, vazio quando o log não tem escopo —
        // só ruído em toda linha. Escopo preenchido (ex.: 'api') continua aparecendo.
        if (($context['scope'] ?? null) === []) {
            unset($context['scope']);
        }

        $dados = [
            'timestamp' => (new DateTimeImmutable('now', new DateTimeZone($this->getConfig('fuso'))))
                ->format('Y-m-d\\TH:i:s.vP'),
            'nivel' => $level,
            'mensagem' => $message,
            'contexto' => $context,
        ];

        $app = $this->getConfig('app');
        if ($app !== null) {
            $dados['app'] = $app;
        }

        $ambienteEnvVar = $this->getConfig('ambienteEnvVar');
        if ($ambienteEnvVar !== null) {
            $ambiente = getenv($ambienteEnvVar);
            $dados['ambiente'] = $ambiente !== false ? $ambiente : null;
        }

        $tenantEnvVar = $this->getConfig('tenantEnvVar');
        if ($tenantEnvVar !== null) {
            $tenant = getenv($tenantEnvVar);
            $dados['tenant'] = $tenant !== false ? $tenant : null;
        }

        if ($this->getConfig('requisicao')) {
            $dados['requisicao'] = ContextoRequisicao::extrair(Router::getRequest());
        }

        // INVALID_UTF8_SUBSTITUTE: uma mensagem com byte inválido (ex.: dado binário numa exception)
        // faria json_encode() retornar false e a linha de log inteira se perderia.
        return (string)json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
