<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Log\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * CsrfProtectionMiddleware do CakePHP que renova, em requisições GET, um cookie CSRF
 * assinado com outro `SECURITY_SALT`.
 *
 * O middleware original só emite um cookie novo quando ele está ausente. Se o navegador
 * mandar um cookie antigo — o `SECURITY_SALT` foi trocado (rotação após vazamento,
 * reinstalação no mesmo domínio) —, o GET reaproveita esse cookie para gerar o
 * `_csrfToken` do formulário e o POST seguinte falha com "Missing or invalid CSRF cookie."
 * até o cookie expirar ou o usuário apagá-lo à mão.
 *
 * Só o GET é tratado: um POST com cookie inválido continua sendo rejeitado.
 * Aceita as mesmas opções do CsrfProtectionMiddleware do CakePHP.
 *
 * @see https://book.cakephp.org/5/en/security/csrf.html
 */
class CsrfProtectionKawMiddleware extends CsrfProtectionMiddleware
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return parent::process($this->descartaCookieInvalido($request), $handler);
    }

    /**
     * Remove do request o cookie CSRF cuja assinatura (HMAC) não confere com o salt atual,
     * fazendo o middleware original tratá-lo como ausente e emitir um novo.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    private function descartaCookieInvalido(ServerRequestInterface $request): ServerRequestInterface
    {
        if ($request->getMethod() !== 'GET') {
            return $request;
        }

        $cookies = $request->getCookieParams();
        $nomeCookie = $this->_config['cookieName'];
        $valor = $cookies[$nomeCookie] ?? null;

        if (!is_string($valor) || $valor === '' || $this->_verifyToken($valor)) {
            return $request;
        }

        // Não é erro — o fluxo se recupera sozinho —, mas a frequência ajuda a perceber
        // problemas de configuração (ex.: SECURITY_SALT trocado sem querer).
        Log::info('Cookie CSRF com assinatura inválida descartado; um novo será emitido', [
            'cookie' => $nomeCookie,
        ]);

        unset($cookies[$nomeCookie]);

        return $request->withCookieParams($cookies);
    }
}
