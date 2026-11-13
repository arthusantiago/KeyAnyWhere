<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Normaliza o e-mail do POST do login (sem espaços nas pontas e em minúsculas) antes do
 * AuthenticationMiddleware: o e-mail é gravado em minúsculas (User::_setEmail()), e a busca do
 * usuário compara o texto como foi digitado. Sem isso, `Pessoa@Exemplo.com` ou um espaço no fim
 * (comum no teclado do celular) não entram.
 *
 * Fica depois do BodyParserMiddleware, que preenche o corpo da requisição.
 */
class EmailLoginNormalizadoMiddleware implements MiddlewareInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $corpo = $request->getParsedBody();
        if (
            $request->getMethod() === 'POST'
            && $request->getUri()->getPath() === Router::url('/users/login')
            && is_array($corpo)
            && isset($corpo['email'])
            && is_string($corpo['email'])
        ) {
            // strtolower, como o User::_setEmail(): a mesma regra da gravação.
            $corpo['email'] = strtolower(trim($corpo['email']));
            $request = $request->withParsedBody($corpo);
        }

        return $handler->handle($request);
    }
}
