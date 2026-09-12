<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\ErrorController;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Exception;

/**
 * App\Controller\ErrorController Test Case
 *
 * @uses \App\Controller\ErrorController
 */
class ErrorControllerTest extends TestCase
{
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
