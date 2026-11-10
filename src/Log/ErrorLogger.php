<?php
declare(strict_types=1);

namespace App\Log;

use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Error\Debugger;
use Cake\Error\ErrorLogger as ErrorLoggerCake;
use Cake\Error\PhpError;
use Cake\Http\Exception\HttpException;
use Cake\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * ErrorLogger do CakePHP com saída estruturada, pensado pra ser usado junto do
 * JsonFormatter.
 *
 * O ErrorLogger original junta tudo num texto só (classe, mensagem, arquivo,
 * stack trace, "Request URL", "Referer", "Client IP"), que vira um único campo
 * `mensagem` no JSON — difícil de filtrar no Loki. Aqui:
 * - `mensagem` fica curta: `[Classe] mensagem`;
 * - os detalhes vão no contexto, em campos próprios (`excecao`/`erro`);
 * - os dados da requisição vêm do campo `requisicao` do JsonFormatter;
 * - URL e Referer não são gravados crus (podem carregar segredo — ver ContextoRequisicao).
 *
 * Nível: exceção com código HTTP 4xx (HttpException/CakeException — CSRF
 * inválido, 404, 405...) é erro do cliente, não da aplicação: vai como
 * `warning`, pra não disparar alerta de erro. O resto continua `error`.
 *
 * Uso (config/app.php): 'Error' => ['logger' => \App\Log\ErrorLogger::class, ...]
 */
class ErrorLogger extends ErrorLoggerCake
{
    /**
     * @inheritDoc
     */
    public function logException(
        Throwable $exception,
        ?ServerRequestInterface $request = null,
        bool $includeTrace = false,
    ): void {
        $contexto = ['excecao' => $this->dadosExcecao($exception, $includeTrace)]
            + $this->dadosRequisicao($request)
            + $this->getExceptionContext($exception);

        $this->log(
            $this->nivel($exception),
            sprintf('[%s] %s', $exception::class, $exception->getMessage()),
            $contexto,
        );
    }

    /**
     * @inheritDoc
     */
    public function logError(PhpError $error, ?ServerRequestInterface $request = null, bool $includeTrace = false): void
    {
        $dadosErro = [
            'tipo' => $error->getLabel(),
            'mensagem' => $error->getMessage(),
            'arquivo' => $error->getFile(),
            'linha' => $error->getLine(),
        ];
        if ($includeTrace) {
            $dadosErro['trace'] = explode("\n", trim($error->getTraceAsString()));
        }

        $nivel = match ($error->getLabel()) {
            'strict' => LOG_NOTICE,
            'deprecated' => LOG_DEBUG,
            default => $error->getLabel(),
        };

        $this->log($nivel, $error->getMessage(), ['erro' => $dadosErro] + $this->dadosRequisicao($request));
    }

    /**
     * @return array<string, mixed>
     */
    private function dadosExcecao(Throwable $exception, bool $incluirTrace): array
    {
        $dados = [
            'classe' => $exception::class,
            'mensagem' => $exception->getMessage(),
            'codigo' => $exception->getCode(),
            'arquivo' => $exception->getFile(),
            'linha' => $exception->getLine(),
        ];

        if ($incluirTrace) {
            $dados['trace'] = $this->trace($exception);
        }

        $anterior = $exception->getPrevious();
        if ($anterior !== null) {
            $dados['anterior'] = $this->dadosExcecao($anterior, $incluirTrace);
        }

        return $dados;
    }

    /**
     * Mesmo formato curto do ErrorLogger original ("CORE/src/...:165"), só que como lista.
     *
     * @return list<string>
     */
    private function trace(Throwable $exception): array
    {
        $trace = Debugger::formatTrace(
            $exception,
            ['format' => Configure::read('Error.traceFormat', 'shortPoints')],
        );

        $linhas = [];
        foreach (is_array($trace) ? $trace : [] as $linha) {
            $linhas[] = is_string($linha) ? trim($linha) : $linha['file'] . ':' . $linha['line'];
        }

        return $linhas;
    }

    /**
     * O campo `requisicao` do JsonFormatter vem de Router::getRequest(), que só existe depois
     * do RoutingMiddleware. Exceção anterior a isso (ou de rota inexistente) não teria dado
     * nenhum da requisição — nesse caso eles vêm aqui, a partir do request do ErrorHandler.
     *
     * @return array<string, mixed>
     */
    private function dadosRequisicao(?ServerRequestInterface $request): array
    {
        if ($request === null) {
            return [];
        }

        $dados = ['referer_origem' => ContextoRequisicao::origemReferer($request)];

        if (Router::getRequest() === null) {
            $dados['requisicao'] = ContextoRequisicao::extrair($request);
        }

        return $dados;
    }

    /**
     * `warning` para exceção HTTP 4xx (erro do cliente), `error` para o resto.
     *
     * @param \Throwable $exception
     * @return string
     */
    private function nivel(Throwable $exception): string
    {
        $codigo = $exception instanceof HttpException || $exception instanceof CakeException
            ? (int)$exception->getCode()
            : 0;

        return $codigo >= 400 && $codigo < 500 ? 'warning' : 'error';
    }
}
