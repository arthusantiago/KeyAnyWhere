<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Middleware\SessionsKawMiddleware;
use Authentication\Identity;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Chama o middleware direto, sem a pilha HTTP: o usuário vem no atributo `identity`, como o
 * AuthenticationMiddleware deixa. Em CLI o id da sessão é sempre 'cli' (tests/bootstrap.php).
 *
 * @uses \App\Middleware\SessionsKawMiddleware
 */
class SessionsKawMiddlewareTest extends TestCase
{
    use LocatorAwareTrait;

    protected array $fixtures = [
        'app.Users',
        'app.Sessions',
        'app.Auditorias',
    ];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        // A fixture tem um registro de sessão; cada teste monta só o que precisa.
        $this->fetchTable('Sessions')->deleteAll([]);
    }

    /**
     * Insere uma sessão direto via SQL (o `id_secundario` tem default no banco).
     *
     * @param string $id
     * @param array<string, mixed> $campos
     * @return void
     */
    private function inserirSessao(string $id, array $campos = []): void
    {
        $agora = DateTime::now()->i18nFormat('yyyy-MM-dd HH:mm:ss');
        ConnectionManager::get('test')->insert('sessions', ['id' => $id] + $campos + [
            'user_id' => null,
            'user_agent' => null,
            'expires' => time() + 3600,
            'data' => null,
            'created' => $agora,
            'modified' => $agora,
        ]);
    }

    /**
     * @param bool $autenticado
     * @param string $userAgent
     * @return \Cake\Http\ServerRequest
     */
    private function requisicao(bool $autenticado, string $userAgent = 'Agente de Teste'): ServerRequest
    {
        $request = (new ServerRequest(['url' => '/']))->withHeader('User-Agent', $userAgent);
        if ($autenticado) {
            $request = $request->withAttribute('identity', new Identity($this->fetchTable('Users')->get(1)));
        }

        return $request;
    }

    /**
     * @param \Cake\Http\ServerRequest $request
     * @param \App\Middleware\SessionsKawMiddleware|null $middleware
     * @return \Cake\Http\Response
     */
    private function processar(ServerRequest $request, ?SessionsKawMiddleware $middleware = null): Response
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response(['body' => 'resposta']));
        $resposta = ($middleware ?? new SessionsKawMiddleware())->process($request, $handler);
        $this->assertInstanceOf(Response::class, $resposta);

        return $resposta;
    }

    /**
     * Logo após o login o handler de sessão do PHP ainda não gravou o registro: ele é criado com o
     * usuário e o user-agent.
     *
     * @return void
     */
    public function testAutenticadoSemSessaoGravadaCriaORegistro(): void
    {
        $this->processar($this->requisicao(true));

        $sessao = $this->fetchTable('Sessions')->get('cli');
        $this->assertSame(1, $sessao->get('user_id'));
        $this->assertSame('Agente de Teste', $sessao->get('user_agent'));
    }

    /**
     * Registro já gravado pelo handler de sessão, ainda sem os dados do usuário.
     *
     * @return void
     */
    public function testAutenticadoComSessaoSemUsuarioCompletaOsCampos(): void
    {
        $this->inserirSessao('cli');

        $this->processar($this->requisicao(true));

        $sessao = $this->fetchTable('Sessions')->get('cli');
        $this->assertSame(1, $sessao->get('user_id'));
        $this->assertSame('Agente de Teste', $sessao->get('user_agent'));
    }

    /**
     * @return void
     */
    public function testAutenticadoComSessaoJaPreenchidaNaoSobrescreve(): void
    {
        $this->inserirSessao('cli', ['user_id' => 1, 'user_agent' => 'Agente Original']);

        $this->processar($this->requisicao(true, 'Agente Novo'));

        $this->assertSame('Agente Original', $this->fetchTable('Sessions')->get('cli')->get('user_agent'));
    }

    /**
     * Sem usuário, só as sessões vencidas (pelo `expires`) são apagadas, e nenhuma é criada.
     *
     * @return void
     */
    public function testNaoAutenticadoApagaSoAsSessoesVencidas(): void
    {
        $this->inserirSessao('vencida', ['expires' => time() - 60]);
        $this->inserirSessao('ativa', ['expires' => time() + 3600]);
        // Criada há dias, mas ainda ativa: não é apagada
        $this->inserirSessao('antiga-ativa', [
            'expires' => time() + 3600,
            'created' => DateTime::now()->subDays(3)->i18nFormat('yyyy-MM-dd HH:mm:ss'),
        ]);

        $this->processar($this->requisicao(false));

        $sessoes = $this->fetchTable('Sessions');
        $this->assertFalse($sessoes->exists(['id' => 'vencida']));
        $this->assertTrue($sessoes->exists(['id' => 'ativa']));
        $this->assertTrue($sessoes->exists(['id' => 'antiga-ativa']));
        $this->assertFalse($sessoes->exists(['id' => 'cli']));
    }

    /**
     * @return void
     */
    public function testDevolveARespostaDoHandler(): void
    {
        $this->assertSame('resposta', (string)$this->processar($this->requisicao(false))->getBody());
    }

    /**
     * A mesma instância atende requisições seguidas sem levar dados de uma para a outra.
     *
     * @return void
     */
    public function testMesmaInstanciaNaoMisturaRequisicoes(): void
    {
        $middleware = new SessionsKawMiddleware();
        $this->processar($this->requisicao(true, 'Primeiro'), $middleware);
        $this->inserirSessao('vencida', ['expires' => time() - 60]);

        $this->processar($this->requisicao(false), $middleware);

        $sessoes = $this->fetchTable('Sessions');
        $this->assertSame('Primeiro', $sessoes->get('cli')->get('user_agent'));
        $this->assertFalse($sessoes->exists(['id' => 'vencida']));
    }
}
