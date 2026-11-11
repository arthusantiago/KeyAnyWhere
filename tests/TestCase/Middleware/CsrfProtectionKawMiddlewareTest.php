<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Middleware\CsrfProtectionKawMiddleware;
use Cake\Http\Exception\InvalidCsrfTokenException;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Log\Engine\ArrayLog;
use Cake\Log\Log;
use Cake\TestSuite\TestCase;
use Cake\Utility\Security;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Cenário: o navegador ainda tem o cookie CSRF emitido antes de o SECURITY_SALT ser trocado
 * (mesmo domínio, outro salt). Sem a renovação no GET, todo POST falharia com
 * "Missing or invalid CSRF cookie." até o cookie expirar.
 *
 * Testado direto no middleware: nos testes de integração o CSRF é pulado
 * (Application::isRunningUnitTests()), inclusive a emissão do cookie.
 *
 * @uses \App\Middleware\CsrfProtectionKawMiddleware
 */
class CsrfProtectionKawMiddlewareTest extends TestCase
{
    private const COOKIE = 'csrfToken';

    private string $saltOriginal;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->saltOriginal = Security::getSalt();
        Security::setSalt(str_repeat('salt-da-instalacao-atual', 4));
        Log::setConfig('captura', ['className' => ArrayLog::class, 'levels' => ['info']]);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        Log::drop('captura');
        Security::setSalt($this->saltOriginal);
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testGetComCookieDeOutroSaltEmiteCookieNovo(): void
    {
        $cookieAntigo = $this->tokenAssinadoComOutroSalt();

        $response = (new CsrfProtectionKawMiddleware())->process($this->request('GET', $cookieAntigo), $this->handler());
        $this->assertInstanceOf(Response::class, $response);

        $cookieNovo = $response->getCookie(self::COOKIE);
        $this->assertNotNull($cookieNovo, 'Um cookie CSRF novo deveria ter sido emitido.');
        $this->assertNotSame($cookieAntigo, $cookieNovo['value']);
        $this->assertCount(1, $this->logsCapturados());
        $this->assertStringContainsString(
            'Cookie CSRF com assinatura inválida descartado',
            $this->logsCapturados()[0],
        );

        // O cookie novo precisa ser aceito no POST seguinte (é o que destrava o formulário).
        $post = $this->request('POST', $cookieNovo['value'], ['_csrfToken' => $cookieNovo['value']]);
        $resultado = (new CsrfProtectionKawMiddleware())->process($post, $this->handler());
        $this->assertSame(200, $resultado->getStatusCode());
    }

    /**
     * @return void
     */
    public function testGetComCookieValidoMantemCookie(): void
    {
        $cookieValido = (new CsrfProtectionKawMiddleware())->createToken();

        $response = (new CsrfProtectionKawMiddleware())->process($this->request('GET', $cookieValido), $this->handler());
        $this->assertInstanceOf(Response::class, $response);

        $this->assertNull($response->getCookie(self::COOKIE), 'Cookie válido não deve ser substituído.');
        $this->assertSame([], $this->logsCapturados());
    }

    /**
     * Sem cookie, o comportamento é o do CakePHP: emite um cookie novo, sem log.
     *
     * @return void
     */
    public function testGetSemCookieEmiteCookieSemLog(): void
    {
        $request = new ServerRequest(['environment' => ['REQUEST_METHOD' => 'GET']]);

        $response = (new CsrfProtectionKawMiddleware())->process($request, $this->handler());
        $this->assertInstanceOf(Response::class, $response);

        $this->assertNotNull($response->getCookie(self::COOKIE));
        $this->assertSame([], $this->logsCapturados());
    }

    /**
     * @return void
     */
    public function testPostComCookieDeOutroSaltContinuaRejeitado(): void
    {
        $cookieAntigo = $this->tokenAssinadoComOutroSalt();

        $this->expectException(InvalidCsrfTokenException::class);
        $this->expectExceptionMessage('Missing or invalid CSRF cookie.');

        (new CsrfProtectionKawMiddleware())->process(
            $this->request('POST', $cookieAntigo, ['_csrfToken' => $cookieAntigo]),
            $this->handler(),
        );
    }

    /**
     * @return list<string>
     */
    private function logsCapturados(): array
    {
        $engine = Log::engine('captura');
        $this->assertInstanceOf(ArrayLog::class, $engine);

        return array_values($engine->read());
    }

    /**
     * @return string
     */
    private function tokenAssinadoComOutroSalt(): string
    {
        $saltAtual = Security::getSalt();
        Security::setSalt(str_repeat('salt-de-outra-instalacao', 4));

        try {
            return (new CsrfProtectionKawMiddleware())->createToken();
        } finally {
            Security::setSalt($saltAtual);
        }
    }

    /**
     * @param string $metodo
     * @param string $cookie
     * @param array<string, mixed> $dados
     * @return \Cake\Http\ServerRequest
     */
    private function request(string $metodo, string $cookie, array $dados = []): ServerRequest
    {
        return new ServerRequest([
            'environment' => ['REQUEST_METHOD' => $metodo],
            'cookies' => [self::COOKIE => $cookie],
            'post' => $dados,
        ]);
    }

    /**
     * @return \Psr\Http\Server\RequestHandlerInterface
     */
    private function handler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            /**
             * @param \Psr\Http\Message\ServerRequestInterface $request
             * @return \Psr\Http\Message\ResponseInterface
             */
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
    }
}
