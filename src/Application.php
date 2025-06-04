<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use App\Middleware\SecurityHeadersKawMiddleware;
use App\Middleware\SessionsKawMiddleware;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\Middleware\HttpsEnforcerMiddleware;
use Cake\Http\Middleware\SecurityHeadersMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 *
 * @extends \Cake\Http\BaseApplication<\App\Application>
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    public const DESENVOLVIMENTO = 0;
    public const PRODUCAO = 1;
    public const SANDBOX = 2;
    public const TESTE = 3;

    /**
     * Analisa em qual ambiente (desenvolvimento/producao/teste e etc) a aplicação está executando.
     * O comportamento padrão é comparar o ambiente perguntado via parametro com o ambiente de execução corrente,
     * e responder se é igual ou não.
     * Se for informado $isEnvironment === false, será retornado o ambiente corrente.
     *
     * @access public static
     * @param bool $isEnvironment Default: self::PRODUCAO
     * @return int|bool
     */
    public static function isTheExecutionEnvironment(int|bool $isEnvironment = self::PRODUCAO)
    {
        // ambiente default
        $ambienteCorrente = self::PRODUCAO;

        if (Configure::read('debug')) {
            $ambienteCorrente = self::DESENVOLVIMENTO;
        }
        /* Adicionar aqui mais validações de ambiente */

        if ($isEnvironment === false) {
            return $ambienteCorrente;
        }

        return $ambienteCorrente === $isEnvironment;
    }
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli();
        } else {
            FactoryLocator::add(
                'Table',
                (new TableLocator())->allowFallbackClass(false),
            );
        }

        /*
         * Only try to load DebugKit in development mode
         * Debug Kit should not be installed on a production system
         */
        if (self::isTheExecutionEnvironment(self::DESENVOLVIMENTO)) {
            $this->addPlugin('DebugKit');
        }

        // Load more plugins here
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance.
            // See https://github.com/CakeDC/cakephp-cached-routing
            ->add(new RoutingMiddleware($this))

            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/4/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())
            // add Authentication after RoutingMiddleware
            ->add(new AuthenticationMiddleware($this));

            // Cross Site Request Forgery (CSRF) Protection Middleware
            // https://book.cakephp.org/4/en/controllers/middleware.html#cross-site-request-forgery-csrf-middleware
            $csrfProtecConfig = [
                'cookieName' => self::isTheExecutionEnvironment(self::DESENVOLVIMENTO) ? 'Secure-csrfToken' : '__Secure-csrfToken',
                'httponly' => true,
                'secure' => true,
                'samesite' => 'Strict',
            ];
            $middlewareQueue->add(new CsrfProtectionMiddleware($csrfProtecConfig));

            // Forçando HTTPS em todas as conexões;
            $middlewareQueue->add(new HttpsEnforcerMiddleware([
                'headers' => ['X-Https-Upgrade' => 1],
                'hsts' => [
                    // 31536000 = 60 * 60 * 24 * 365
                    'maxAge' => 31536000,
                    'includeSubDomains' => true,
                    'preload' => true,
                ],
            ]));

            $securityHeaders = new SecurityHeadersKawMiddleware();
            $securityHeaders
                ->setReferrerPolicy(SecurityHeadersMiddleware::STRICT_ORIGIN_WHEN_CROSS_ORIGIN)
                ->setXssProtection(SecurityHeadersMiddleware::XSS_DISABLED)
                ->setCrossDomainPolicy(SecurityHeadersMiddleware::NONE)
                ->noSniff()
                ->noOpen();

        if (self::isTheExecutionEnvironment()) {
            $securityHeaders->setXFrameOptions(SecurityHeadersMiddleware::DENY);
            $securityHeaders->setContentSecurityPolicy();
        }

            $middlewareQueue->add($securityHeaders);
            $middlewareQueue->add(new SessionsKawMiddleware($this));

        return $middlewareQueue;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/4/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
    }

    /**
     * Bootstrapping for CLI application.
     *
     * That is when running commands.
     *
     * @return void
     */
    protected function bootstrapCli(): void
    {
        $this->addOptionalPlugin('Cake/Repl');
        $this->addOptionalPlugin('Bake');

        $this->addPlugin('Migrations');

        // Load more plugins here
    }

    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $authenticationService = new AuthenticationService([
            'unauthenticatedRedirect' => Router::url('/users/login'),
            'queryParam' => 'redirect',
        ]);

        // Load identifiers, ensure we check email and password fields
        $authenticationService->loadIdentifier('Authentication.Password', [
            'fields' => [
                'username' => 'email',
                'password' => 'password',
            ],
        ]);

        // Load the authenticators, you want session first
        $authenticationService->loadAuthenticator('Authentication.Session');
        // Configure form data check to pick email and password
        $authenticationService->loadAuthenticator('Authentication.Form', [
            'fields' => [
                'username' => 'email',
                'password' => 'password',
            ],
            'loginUrl' => Router::url('/users/login'),
        ]);

        return $authenticationService;
    }
}
