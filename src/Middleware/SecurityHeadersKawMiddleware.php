<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Middleware\SecurityHeadersMiddleware;

/**
 * Utilizando a estrutuda do SecurityHeadersMiddleware para ampliar as configurações de segurança.
 *
 * @see	https://book.cakephp.org/4/en/security/security-headers.html
 * @global
 */
class SecurityHeadersKawMiddleware extends SecurityHeadersMiddleware
{
    /**
     * Se nenhum parâmetro for informado, é setado uma configuração padrão.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy
     * @access public
     * @param string $policy
     * @return	SecurityHeadersKawMiddleware $this
     */
    public function setContentSecurityPolicy(string $policy = '')
    {
        if (empty($policy)) {
            $policy = 'default-src https: \'self\'; img-src \'self\'; script-src \'self\'; style-src \'self\'; object-src \'none\'';
        }

        $this->headers['content-security-policy'] = $policy;

        return $this;
    }
}
