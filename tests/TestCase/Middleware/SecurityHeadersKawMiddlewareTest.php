<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Middleware\SecurityHeadersKawMiddleware;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * App\Middleware\SecurityHeadersKawMiddleware Test Case
 *
 * @uses \App\Middleware\SecurityHeadersKawMiddleware
 */
class SecurityHeadersKawMiddlewareTest extends TestCase
{
    private SecurityHeadersKawMiddleware $middleware;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SecurityHeadersKawMiddleware();
    }

    /**
     * Test setContentSecurityPolicy - Sets default policy
     *
     * @return void
     */
    public function testSetContentSecurityPolicySetsDefaultPolicy(): void
    {
        $result = $this->middleware->setContentSecurityPolicy();

        // Should return $this for fluent interface
        $this->assertSame($this->middleware, $result);

        // Process a request to verify headers are set
        $request = new ServerRequest(['url' => '/']);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());

        $response = $this->middleware->process($request, $handler);

        // Check that default CSP header is set
        $this->assertNotNull($response->getHeader('Content-Security-Policy'));
        $cspHeader = $response->getHeaderLine('Content-Security-Policy');
        $this->assertStringContainsString('default-src https:', $cspHeader);
        $this->assertStringContainsString("'self'", $cspHeader);
        $this->assertStringContainsString("'none'", $cspHeader);
    }

    /**
     * Test setContentSecurityPolicy - Default policy contains expected directives
     *
     * @return void
     */
    public function testDefaultPolicyContainsExpectedDirectives(): void
    {
        $this->middleware->setContentSecurityPolicy();

        $request = new ServerRequest(['url' => '/']);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());

        $response = $this->middleware->process($request, $handler);

        $cspHeader = $response->getHeaderLine('Content-Security-Policy');

        // Verify default policy directives
        $this->assertStringContainsString('default-src https:', $cspHeader);
        $this->assertStringContainsString("img-src 'self'", $cspHeader);
        $this->assertStringContainsString("script-src 'self'", $cspHeader);
        $this->assertStringContainsString("style-src 'self'", $cspHeader);
        $this->assertStringContainsString("object-src 'none'", $cspHeader);
    }

    /**
     * Test setContentSecurityPolicy - Fluent interface chaining
     *
     * @return void
     */
    public function testFluentInterfaceChaining(): void
    {
        // Should allow method chaining
        $result = $this->middleware
            ->setContentSecurityPolicy()
            ->setContentSecurityPolicy();

        $this->assertSame($this->middleware, $result);
    }

    /**
     * Test process - Returns response from handler
     *
     * @return void
     */
    public function testProcessReturnsResponseFromHandler(): void
    {
        $request = new ServerRequest(['url' => '/']);
        $expectedResponse = new Response(['body' => 'test']);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);

        $this->assertSame($expectedResponse, $response);
    }

    /**
     * Test process - Handler is called with request
     *
     * @return void
     */
    public function testProcessCallsHandlerWithRequest(): void
    {
        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $handlerCalled = false;
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturnCallback(function ($passedRequest) use ($request, &$handlerCalled) {
                $handlerCalled = true;
                $this->assertSame($request, $passedRequest);

                return new Response();
            });

        $this->middleware->process($request, $handler);

        $this->assertTrue($handlerCalled);
    }

    /**
     * Test setContentSecurityPolicy - Returns instance for fluent interface
     *
     * @return void
     */
    public function testSetContentSecurityPolicyReturnsSelf(): void
    {
        $middleware = new SecurityHeadersKawMiddleware();
        $result = $middleware->setContentSecurityPolicy();

        // Test fluent interface
        $this->assertInstanceOf(SecurityHeadersKawMiddleware::class, $result);
        $this->assertSame($middleware, $result);
    }
}
