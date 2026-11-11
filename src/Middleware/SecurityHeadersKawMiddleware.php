<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Middleware\SecurityHeadersMiddleware;

/**
 * Utilizando a estrutura do SecurityHeadersMiddleware para ampliar as configurações de segurança.
 *
 * @see https://book.cakephp.org/4/en/security/security-headers.html
 * @global
 */
class SecurityHeadersKawMiddleware extends SecurityHeadersMiddleware
{
    /**
     * Aplica a politica padrão de carregamento do conteúdo.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy
     * @access public
     * @return \App\Middleware\SecurityHeadersKawMiddleware $this
     */
    public function setContentSecurityPolicy(): SecurityHeadersKawMiddleware
    {
        $policy = "default-src https: 'self'; "
        // data: é necessário para o QrCode do 2FA (UsersController::geraQrCode2fa() devolve um data URI
        // SVG inserido via <img>). SVG carregado em <img> não executa scripts.
        . "img-src 'self' data:; "
        . "script-src 'self'; "
        . "style-src 'self'; "
        . "object-src 'none'; ";

        $this->headers['content-security-policy'] = $policy;

        return $this;
    }
}
