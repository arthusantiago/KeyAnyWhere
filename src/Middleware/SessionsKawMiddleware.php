<?php
declare(strict_types=1);

namespace App\Middleware;

use Authentication\IdentityInterface;
use Cake\Datasource\EntityInterface;
use Cake\Http\ServerRequest;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Table;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Completa o registro da sessão no banco (tabela `sessions`) com o usuário autenticado e o
 * user-agent, usados na lista de sessões de "Minha conta"; nas requisições sem usuário, apaga as
 * sessões vencidas.
 *
 * ATENÇÃO: deve entrar na fila depois do AuthenticationMiddleware. O usuário vem do atributo
 * `identity` que ele grava na request; sem ele, toda requisição é tratada como anônima.
 *
 * Não guarda nada da requisição em propriedades: a mesma instância atende requisições seguidas.
 */
class SessionsKawMiddleware implements MiddlewareInterface
{
    use LocatorAwareTrait;

    /**
     * Atributo em que o AuthenticationMiddleware grava o usuário (configuração padrão dele).
     */
    private const ATRIBUTO_IDENTIDADE = 'identity';

    /**
     * @param string $tabela Tabela dos registros de sessão.
     */
    public function __construct(private string $tabela = 'sessions')
    {
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $sessoes = $this->getTableLocator()->get($this->tabela);
        $identidade = $request->getAttribute(self::ATRIBUTO_IDENTIDADE);

        if ($identidade instanceof IdentityInterface && $request instanceof ServerRequest) {
            $this->completarSessao($sessoes, $request, $identidade);
        } else {
            $this->apagarSessoesVencidas($sessoes);
        }

        return $handler->handle($request);
    }

    /**
     * Grava usuário e user-agent só quando faltam: uma sessão já preenchida não é regravada.
     *
     * @param \Cake\ORM\Table $sessoes
     * @param \Cake\Http\ServerRequest $request
     * @param \Authentication\IdentityInterface $identidade
     * @return void
     */
    private function completarSessao(Table $sessoes, ServerRequest $request, IdentityInterface $identidade): void
    {
        $sessao = $this->sessaoSalvaNoBanco($sessoes, $request);

        if ($sessao->get('user_id') && $sessao->get('user_agent')) {
            return;
        }

        $sessao->set('user_id', $sessao->get('user_id') ?: $identidade->getIdentifier());
        $sessao->set('user_agent', $sessao->get('user_agent') ?: $request->getHeaderLine('User-Agent'));
        $sessoes->save($sessao);
    }

    /**
     * Registro da sessão atual. Se o handler de sessão do PHP ainda não o gravou (ele grava só no
     * fechamento da sessão, no fim da requisição; acontece logo após o login, quando o
     * Authentication.Session renova o id da sessão na própria requisição), devolve uma entidade
     * nova com a chave primária preenchida.
     *
     * @param \Cake\ORM\Table $sessoes
     * @param \Cake\Http\ServerRequest $request
     * @return \Cake\Datasource\EntityInterface
     */
    private function sessaoSalvaNoBanco(Table $sessoes, ServerRequest $request): EntityInterface
    {
        $idSessao = $request->getSession()->id();
        $chave = $sessoes->getPrimaryKey();
        $chave = is_array($chave) ? (string)$chave[0] : $chave;

        $sessao = $sessoes->find()->where([$chave => $idSessao])->first();
        if ($sessao instanceof EntityInterface) {
            return $sessao;
        }

        $sessao = $sessoes->newEmptyEntity();
        $sessao->set($chave, $idSessao);

        return $sessao;
    }

    /**
     * Pelo `expires`, o mesmo critério do `Cake\Http\Session\DatabaseSession::gc()`: a sessão vence
     * quando passa do tempo de inatividade, não um dia depois de criada.
     *
     * @param \Cake\ORM\Table $sessoes
     * @return void
     */
    private function apagarSessoesVencidas(Table $sessoes): void
    {
        $sessoes->deleteAll(['expires <' => time()]);
    }
}
