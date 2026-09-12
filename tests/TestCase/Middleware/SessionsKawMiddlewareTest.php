<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Middleware\SessionsKawMiddleware;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * App\Middleware\SessionsKawMiddleware Test Case
 *
 * @uses \App\Middleware\SessionsKawMiddleware
 */
class SessionsKawMiddlewareTest extends TestCase
{
    use LocatorAwareTrait;

    protected array $fixtures = [
        'app.Users',
        'app.Sessions',
    ];

    /**
     * Insere uma sessão diretamente via SQL, evitando a necessidade de gerar
     * um UUID válido para 'id_secundario' (a coluna tem um default no banco).
     *
     * @param string $id
     * @param array<string, mixed> $overrides
     * @return void
     */
    private function inserirSessao(string $id, array $overrides = []): void
    {
        $dados = $overrides + [
            'user_id' => null,
            'user_agent' => null,
            'expires' => null,
            'data' => null,
            'created' => DateTime::now()->i18nFormat('yyyy-MM-dd HH:mm:ss'),
            'modified' => DateTime::now()->i18nFormat('yyyy-MM-dd HH:mm:ss'),
        ];

        ConnectionManager::get('test')->insert(
            'sessions',
            ['id' => $id] + $dados,
        );
    }

    /**
     * Test process - Autenticação válida preenche user_id/user_agent quando a sessão ainda não os tem.
     *
     * @return void
     */
    public function testProcessPreenchesSessaoQuandoAutenticado(): void
    {
        $this->inserirSessao('sessao-valida');

        $request = new ServerRequest(['url' => '/', 'session' => null]);
        $request->getSession()->id('sessao-valida');
        $request = $request->withHeader('User-Agent', 'Agente de Teste');

        $user = $this->fetchTable('Users')->get(1);

        $result = new Result($user, Result::SUCCESS);
        $authService = $this->createMock(AuthenticationServiceInterface::class);
        $authService->method('authenticate')->willReturn($result);

        $subject = $this->createMock(AuthenticationServiceProviderInterface::class);
        $subject->method('getAuthenticationService')->willReturn($authService);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response());

        $middleware = new SessionsKawMiddleware($subject);
        $middleware->process($request, $handler);

        $sessao = $this->fetchTable('Sessions')->get('sessao-valida');

        $this->assertEquals(1, $sessao->user_id);
        $this->assertNotEmpty($sessao->user_agent);
    }

    /**
     * Test process - Não sobrescreve user_id/user_agent quando a sessão já os possui.
     *
     * @return void
     */
    public function testProcessNaoSobrescreveSessaoJaPreenchida(): void
    {
        $this->inserirSessao('sessao-preenchida', [
            'user_id' => 1,
            'user_agent' => 'Agente Original',
        ]);

        $request = new ServerRequest(['url' => '/', 'session' => null]);
        $request->getSession()->id('sessao-preenchida');
        $request = $request->withHeader('User-Agent', 'Agente Novo');

        $user = $this->fetchTable('Users')->get(1);

        $result = new Result($user, Result::SUCCESS);
        $authService = $this->createMock(AuthenticationServiceInterface::class);
        $authService->method('authenticate')->willReturn($result);

        $subject = $this->createMock(AuthenticationServiceProviderInterface::class);
        $subject->method('getAuthenticationService')->willReturn($authService);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response());

        $middleware = new SessionsKawMiddleware($subject);
        $middleware->process($request, $handler);

        $sessao = $this->fetchTable('Sessions')->get('sessao-preenchida');

        $this->assertEquals('Agente Original', $sessao->user_agent);
    }

    /**
     * Test process - Autenticação inválida remove sessões expiradas (criadas há mais de 1 dia).
     *
     * @return void
     */
    public function testProcessRemoveSessoesAntigasQuandoNaoAutenticado(): void
    {
        $antiga = DateTime::now()->subDays(2)->i18nFormat('yyyy-MM-dd HH:mm:ss');
        $recente = DateTime::now()->i18nFormat('yyyy-MM-dd HH:mm:ss');

        $this->inserirSessao('sessao-antiga', ['created' => $antiga, 'modified' => $antiga]);
        $this->inserirSessao('sessao-recente', ['created' => $recente, 'modified' => $recente]);

        $request = new ServerRequest(['url' => '/', 'session' => null]);
        $request->getSession()->id('sessao-antiga');

        $result = $this->createMock(ResultInterface::class);
        $result->method('isValid')->willReturn(false);

        $authService = $this->createMock(AuthenticationServiceInterface::class);
        $authService->method('authenticate')->willReturn($result);

        $subject = $this->createMock(AuthenticationServiceProviderInterface::class);
        $subject->method('getAuthenticationService')->willReturn($authService);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response());

        $middleware = new SessionsKawMiddleware($subject);
        $middleware->process($request, $handler);

        $sessions = $this->fetchTable('Sessions');
        $this->assertFalse($sessions->exists(['id' => 'sessao-antiga']));
        $this->assertTrue($sessions->exists(['id' => 'sessao-recente']));
    }

    /**
     * Test que o middleware chama o handler e retorna a resposta produzida por ele.
     *
     * @return void
     */
    public function testProcessRetornaRespostaDoHandler(): void
    {
        $this->inserirSessao('sessao-resposta');

        $request = new ServerRequest(['url' => '/', 'session' => null]);
        $request->getSession()->id('sessao-resposta');
        $request = $request->withHeader('User-Agent', 'Agente de Teste');

        $user = $this->fetchTable('Users')->get(1);
        $result = new Result($user, Result::SUCCESS);
        $authService = $this->createMock(AuthenticationServiceInterface::class);
        $authService->method('authenticate')->willReturn($result);

        $subject = $this->createMock(AuthenticationServiceProviderInterface::class);
        $subject->method('getAuthenticationService')->willReturn($authService);

        $expectedResponse = new Response(['body' => 'resposta-esperada']);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn($expectedResponse);

        $middleware = new SessionsKawMiddleware($subject);
        $response = $middleware->process($request, $handler);

        $this->assertSame($expectedResponse, $response);
    }
}
