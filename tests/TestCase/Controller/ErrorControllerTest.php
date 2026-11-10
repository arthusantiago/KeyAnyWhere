<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\ErrorController;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Exception;

/**
 * App\Controller\ErrorController Test Case
 *
 * @uses \App\Controller\ErrorController
 */
class ErrorControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * As páginas de erro usam o layout público `falha` (sem menu e sem dados da sessão).
     *
     * @return void
     */
    public function testInitializeUsaOLayoutFalha(): void
    {
        $controller = new ErrorController(new ServerRequest(['url' => '/']));
        $controller->initialize();

        $this->assertSame('falha', $controller->viewBuilder()->getLayout());
    }

    /**
     * Em produção (debug desligado), uma URL inexistente mostra a página de erro com o layout `falha`.
     * Antes, os templates de erro pediam um layout `error` que não existe em templates/layout.
     *
     * @return void
     */
    public function testUrlInexistenteEmProducaoRenderizaPaginaDeErro(): void
    {
        $debugOriginal = Configure::read('debug');
        Configure::write('debug', false);
        // Com debug desligado, o HttpsEnforcerMiddleware exige HTTPS.
        $this->configRequest(['environment' => ['HTTPS' => 'on']]);

        try {
            $this->get('https://localhost/pagina-que-nao-existe');
        } finally {
            Configure::write('debug', $debugOriginal);
        }

        $this->assertResponseCode(404);
        $this->assertResponseContains('Erro ao acessar a URL');
        $this->assertResponseContains('Voltar a página inicial');
        $this->assertResponseNotContains('dropdown-menu');
    }

    /**
     * Test beforeFilter method - should be a no-op (does not call AppController::beforeFilter,
     * so the IP-block check does not run for error pages).
     *
     * @return void
     */
    public function testBeforeFilter(): void
    {
        $controller = new ErrorController(
            new ServerRequest(['url' => '/']),
        );

        $event = new Event('Controller.beforeFilter', $controller);

        try {
            $result = $controller->beforeFilter($event);
            $this->assertNull($result);
        } catch (Exception $e) {
            $this->fail('beforeFilter should not throw exception');
        }
    }

    /**
     * Test beforeRender method sets the 'Error' template path.
     *
     * @return void
     */
    public function testBeforeRenderSetsTemplatePath(): void
    {
        $request = new ServerRequest(['url' => '/error/not-found']);

        $controller = new ErrorController($request);
        $controller->initialize();

        $event = new Event('Controller.beforeRender', $controller);
        $controller->beforeRender($event);

        $viewBuilder = $controller->viewBuilder();
        $this->assertEquals('Error', $viewBuilder->getTemplatePath());
    }

    /**
     * Test afterFilter method - should be a no-op.
     *
     * @return void
     */
    public function testAfterFilter(): void
    {
        $controller = new ErrorController(
            new ServerRequest(['url' => '/']),
        );

        $event = new Event('Controller.afterFilter', $controller);

        try {
            $result = $controller->afterFilter($event);
            $this->assertNull($result);
        } catch (Exception $e) {
            $this->fail('afterFilter should not throw exception');
        }
    }

    /**
     * Test controller extends AppController
     *
     * @return void
     */
    public function testControllerExtendsAppController(): void
    {
        $this->assertTrue(is_subclass_of(
            'App\Controller\ErrorController',
            'App\Controller\AppController',
        ));
    }
}
