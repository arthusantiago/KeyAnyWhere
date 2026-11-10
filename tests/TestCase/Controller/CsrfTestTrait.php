<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Application;
use PHPUnit\Framework\Attributes\Before;

/**
 * Envia um token CSRF válido em toda requisição dos testes de integração.
 *
 * Os testes validam CSRF como em produção (Application::middleware() não pula mais a
 * verificação em testes) — sem o token, todo POST/PUT/DELETE seria barrado com 403 antes
 * de chegar na regra que o teste quer exercitar (autenticação, permissão, validação...).
 */
trait CsrfTestTrait
{
    /**
     * @return void
     */
    #[Before]
    protected function habilitarTokenCsrf(): void
    {
        $this->enableCsrfToken(Application::nomeCookieCsrf());
    }
}
