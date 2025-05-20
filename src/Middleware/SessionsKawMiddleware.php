<?php
namespace App\Middleware;

use Authentication\AuthenticationServiceProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\I18n\FrozenTime;

/**
 * SessionsKawMiddleware adiciona a sessão salva no banco de dados, algumas informações utilizadas no sistema.
 *
 * ATENÇÃO
 * Esse middleware deve ser adicionado a fila depois do AuthenticationMiddleware, caso contrário um erro será gerado.
 *
 * @global
 */
class SessionsKawMiddleware implements MiddlewareInterface
{
    use LocatorAwareTrait;

    /**
     * É esperado que seja um objeto \App\Application que implementa o AuthenticationServiceProviderInterface.
     *
     * @var \Authentication\AuthenticationServiceProviderInterface $subject
     */
    private $subject = null;

    /**
     * Tabela do banco de dados que armazena as sessões
     *
     * @var string $table
     */
    private $table = 'sessions';

    /**
     * Constructor
     *
     * @param \Authentication\AuthenticationServiceProviderInterface $subject
     * @param string $table
     */
    public function __construct(AuthenticationServiceProviderInterface $subject, string $table = '')
    {
        $this->subject = $subject;

        if ($table) {
            $this->table = $table;
        }
    }

    /**
     *
     * @param \Cake\Http\ServerRequest $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Cake\Http\ServerRequest A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $modelSessions =  $this->getTableLocator()->get($this->table);
        $resultAutenticacao = $this->subject
            ->getAuthenticationService($request)
            ->authenticate($request);

        if ($resultAutenticacao->isValid())
        {
            $idSession = $request->getSession()->id();
            $pkField = $modelSessions->getPrimaryKey();
            $sessionDb = $modelSessions
                ->find()
                ->where([$pkField => $idSession])
                ->first();

            if (!$sessionDb->user_id || !$sessionDb->user_agent)
            {
                $userAutenticado = $resultAutenticacao->getData();
                $sessionDb->user_id = $userAutenticado->id;
                $sessionDb->user_agent = $request->getHeaders()['User-Agent'][0];
                $modelSessions->save($sessionDb);
            }
        } else {
            $dataHora = \Cake\I18n\DateTime::now();
            $dataHora = $dataHora->subDays(1, 1);
            $modelSessions->deleteAll([
                'created <= ' => $dataHora->i18nFormat('yyyy-MM-dd 01:00:00'),
            ]);
        }

        return $handler->handle($request);
    }
}
