<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Test\TestCase;

use App\Application;
use Authentication\AuthenticationService;
use Cake\Core\Configure;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Http\ServerRequest;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * ApplicationTest class
 */
class ApplicationTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Test bootstrap in production.
     *
     * @return void
     */
    public function testBootstrap()
    {
        Configure::write('debug', false);
        $app = new Application(dirname(dirname(__DIR__)) . '/config');
        $app->bootstrap();
        $plugins = $app->getPlugins();

        $this->assertTrue($plugins->has('Bake'), 'plugins has Bake?');
        $this->assertFalse($plugins->has('DebugKit'), 'plugins has DebugKit?');
        $this->assertTrue($plugins->has('Migrations'), 'plugins has Migrations?');
    }

    /**
     * Test bootstrap add DebugKit plugin in debug mode.
     *
     * @return void
     */
    public function testBootstrapInDebug()
    {
        Configure::write('debug', true);
        $app = new Application(dirname(dirname(__DIR__)) . '/config');
        $app->bootstrap();
        $plugins = $app->getPlugins();

        $this->assertTrue($plugins->has('DebugKit'), 'plugins has DebugKit?');
    }

    /**
     * testBootstrapPluginWitoutHalt
     *
     * @return void
     */
    public function testBootstrapPluginWithoutHalt()
    {
        $this->expectException(InvalidArgumentException::class);

        $app = $this->getMockBuilder(Application::class)
            ->setConstructorArgs([dirname(dirname(__DIR__)) . '/config'])
            ->onlyMethods(['addPlugin'])
            ->getMock();

        $app->method('addPlugin')
            ->will($this->throwException(new InvalidArgumentException('test exception.')));

        $app->bootstrap();
    }

    /**
     * testMiddleware
     *
     * @return void
     */
    public function testMiddleware()
    {
        $app = new Application(dirname(dirname(__DIR__)) . '/config');
        $middleware = new MiddlewareQueue();

        $middleware = $app->middleware($middleware);

        $this->assertInstanceOf(ErrorHandlerMiddleware::class, $middleware->current());
        $middleware->seek(1);
        $this->assertInstanceOf(AssetMiddleware::class, $middleware->current());
        $middleware->seek(2);
        $this->assertInstanceOf(RoutingMiddleware::class, $middleware->current());
    }

    /**
     * Aplicação que se comporta como fora dos testes (em produção, `isRunningUnitTests()` é falso).
     *
     * @return \App\Application
     */
    private function aplicacaoForaDosTestes(): Application
    {
        $app = $this->getMockBuilder(Application::class)
            ->setConstructorArgs([dirname(dirname(__DIR__)) . '/config'])
            ->onlyMethods(['isRunningUnitTests'])
            ->getMock();
        $app->method('isRunningUnitTests')->willReturn(false);

        return $app;
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function caminhosProvider(): array
    {
        return [
            'api raiz' => ['/api', true],
            'api versionada' => ['/api/v1/configuracoes/status-licenca', true],
            'api de plugin' => ['/api/admin/v1/licencas', true],
            'login' => ['/users/login', false],
            'home' => ['/', false],
            'começa com api mas não é a pasta' => ['/apis/x', false],
            'api no meio do caminho' => ['/entradas/api/1', false],
        ];
    }

    /**
     * RN: só o que começa em `/api/` é rota de API.
     *
     * @param string $caminho
     * @param bool $esperado
     * @return void
     */
    #[DataProvider('caminhosProvider')]
    public function testEhRotaApi(string $caminho, bool $esperado): void
    {
        $this->assertSame($esperado, Application::ehRotaApi(new ServerRequest(['url' => $caminho])));
    }

    /**
     * As rotas de API não passam pelo CSRF (autenticam por token); as demais passam.
     *
     * @return void
     */
    public function testCsrfPulaSoAsRotasDeApi(): void
    {
        $app = $this->aplicacaoForaDosTestes();

        $this->assertTrue($app->pulaVerificacaoCsrf(new ServerRequest(['url' => '/api/v1/qualquer'])));
        $this->assertFalse($app->pulaVerificacaoCsrf(new ServerRequest(['url' => '/users/login'])));
    }

    /**
     * Sem identidade, uma rota de API não é redirecionada para o login da aplicação web; as demais são.
     *
     * @return void
     */
    public function testRotaDeApiNaoRedirecionaParaLogin(): void
    {
        $app = $this->aplicacaoForaDosTestes();

        $api = $app->getAuthenticationService(new ServerRequest(['url' => '/api/v1/qualquer']));
        $this->assertInstanceOf(AuthenticationService::class, $api);
        $this->assertNull($api->getConfig('unauthenticatedRedirect'));

        $web = $app->getAuthenticationService(new ServerRequest(['url' => '/entradas']));
        $this->assertInstanceOf(AuthenticationService::class, $web);
        $this->assertSame('/users/login', $web->getConfig('unauthenticatedRedirect'));
    }

    /**
     * `config/plugins.php` soma os plugins de `config/plugins_local.php` quando ele existe.
     *
     * @return void
     */
    public function testPluginsLocaisSaoSomados(): void
    {
        $config = dirname(dirname(__DIR__)) . '/config/';
        $local = $config . 'plugins_local.php';
        if (file_exists($local)) {
            $this->markTestSkipped('Esta instalação já tem um config/plugins_local.php.');
        }

        $this->assertSame([], require $config . 'plugins.php');

        file_put_contents($local, "<?php\nreturn ['PluginDeTeste' => ['optional' => true]];\n");
        try {
            $this->assertSame(['PluginDeTeste' => ['optional' => true]], require $config . 'plugins.php');
        } finally {
            unlink($local);
        }
    }
}
