<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Middleware\EmailLoginNormalizadoMiddleware;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @uses \App\Middleware\EmailLoginNormalizadoMiddleware
 */
class EmailLoginNormalizadoMiddlewareTest extends TestCase
{
    /**
     * Passa a requisição pelo middleware e devolve o corpo que chegou ao próximo da fila.
     *
     * @param \Cake\Http\ServerRequest $request
     * @return mixed
     */
    private function corpoRepassado(ServerRequest $request): mixed
    {
        $handler = new class implements RequestHandlerInterface {
            public ?ServerRequestInterface $recebida = null;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->recebida = $request;

                return new Response();
            }
        };
        (new EmailLoginNormalizadoMiddleware())->process($request, $handler);

        return $handler->recebida?->getParsedBody();
    }

    /**
     * @return void
     */
    public function testPostDoLoginNormalizaOEmailEMantemOResto(): void
    {
        // Senha gerada (e com espaços nas pontas, que não podem sumir): o Snyk recusa senha fixa no código.
        $senha = ' Aa1!' . bin2hex(random_bytes(8)) . ' ';
        $request = new ServerRequest([
            'url' => '/users/login',
            'environment' => ['REQUEST_METHOD' => 'POST'],
            'post' => ['email' => "  Pessoa@Exemplo.COM \t", 'password' => $senha, '2fa' => '123456'],
        ]);

        $this->assertSame(
            ['email' => 'pessoa@exemplo.com', 'password' => $senha, '2fa' => '123456'],
            $this->corpoRepassado($request),
        );
    }

    /**
     * Outra rota, outro método ou o e-mail fora do formato esperado passam sem mudança.
     *
     * @return void
     */
    public function testForaDoPostDoLoginNaoMexeNoCorpo(): void
    {
        $outraRota = new ServerRequest([
            'url' => '/users/add',
            'environment' => ['REQUEST_METHOD' => 'POST'],
            'post' => ['email' => ' Pessoa@Exemplo.com'],
        ]);
        $this->assertSame(['email' => ' Pessoa@Exemplo.com'], $this->corpoRepassado($outraRota));

        $get = new ServerRequest([
            'url' => '/users/login',
            'environment' => ['REQUEST_METHOD' => 'GET'],
            'post' => ['email' => ' Pessoa@Exemplo.com'],
        ]);
        $this->assertSame(['email' => ' Pessoa@Exemplo.com'], $this->corpoRepassado($get));

        $emailNaoTexto = new ServerRequest([
            'url' => '/users/login',
            'environment' => ['REQUEST_METHOD' => 'POST'],
            'post' => ['email' => ['A@B.com']],
        ]);
        $this->assertSame(['email' => ['A@B.com']], $this->corpoRepassado($emailNaoTexto));

        $semEmail = new ServerRequest([
            'url' => '/users/login',
            'environment' => ['REQUEST_METHOD' => 'POST'],
            'post' => ['password' => 'x'],
        ]);
        $this->assertSame(['password' => 'x'], $this->corpoRepassado($semEmail));
    }
}
