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
     * Política padrão de carregamento de conteúdo.
     *
     * `img-src` aceita `data:` para o QrCode do 2FA (UsersController::geraQrCode2fa() devolve um data
     * URI SVG inserido via <img>); SVG carregado em <img> não executa scripts.
     */
    private const CSP_PADRAO = [
        'default-src' => ['https:', "'self'"],
        'img-src' => ["'self'", 'data:'],
        'script-src' => ["'self'"],
        'style-src' => ["'self'"],
        'object-src' => ["'none'"],
    ];

    /**
     * Pede aos buscadores que não indexem nenhuma página do sistema nem sigam os links dela
     * (`X-Robots-Tag: noindex, nofollow`). O KeyAnywhere é um sistema com login: o endereço de uma
     * instalação (ex.: o `demo` ou o `<conta>.keyanywhere.com.br` de um cliente) não deve aparecer na
     * busca, mesmo quando alguém publica o link. O cabeçalho, e não um `robots.txt` com `Disallow`: o
     * buscador bloqueado pelo `robots.txt` não lê a página e ainda pode listar o endereço.
     *
     * @return \App\Middleware\SecurityHeadersKawMiddleware $this
     */
    public function noIndex(): SecurityHeadersKawMiddleware
    {
        $this->headers['x-robots-tag'] = 'noindex, nofollow';

        return $this;
    }

    /**
     * Aplica a política padrão de carregamento de conteúdo, somando as fontes extras informadas por
     * diretiva (configuração `Kaw.Seguranca.csp`, ex.: `['script-src' => ['https://js.stripe.com']]`).
     * Uma diretiva nova (ex.: `frame-src`) entra só com as fontes extras.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy
     * @access public
     * @param array<string, array<string>> $fontesExtras
     * @return \App\Middleware\SecurityHeadersKawMiddleware $this
     */
    public function setContentSecurityPolicy(array $fontesExtras = []): SecurityHeadersKawMiddleware
    {
        $diretivas = self::CSP_PADRAO;
        foreach ($fontesExtras as $diretiva => $fontes) {
            $diretivas[$diretiva] = array_values(array_unique([...$diretivas[$diretiva] ?? [], ...(array)$fontes]));
        }

        $politica = '';
        foreach ($diretivas as $diretiva => $fontes) {
            $politica .= $diretiva . ' ' . implode(' ', $fontes) . '; ';
        }

        $this->headers['content-security-policy'] = rtrim($politica);

        return $this;
    }
}
