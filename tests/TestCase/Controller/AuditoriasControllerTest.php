<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Table\AuditoriasTable;
use App\Model\Table\UsersTable;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\AuditoriasController Test Case
 *
 * Controller somente leitura, restrito a usuários root.
 *
 * @uses \App\Controller\AuditoriasController
 */
class AuditoriasControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use CsrfTestTrait;
    use AuthenticatedTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Auditorias',
        'app.Users',
        'app.Sessions',
    ];

    /**
     * Cria um registro direto na Table (não há tela de criação: os registros vêm do
     * AuditoriaBehavior das outras Tables).
     *
     * @param int|null $usuarioId
     * @return int Id do registro criado.
     */
    private function criaRegistroAuditoria(?int $usuarioId = 1): int
    {
        $auditorias = $this->fetchTable(AuditoriasTable::class);
        $registro = $auditorias->saveOrFail($auditorias->newEntity([
            'contexto' => 'entrada',
            'id_entidade' => 1,
            'acao' => 'criacao',
            'usuario_id' => $usuarioId,
            'alteracoes' => ['titulo' => ['antes' => '', 'depois' => ''], 'password' => ['antes' => '', 'depois' => '']],
        ]));

        return $registro->id;
    }

    /**
     * @return void
     */
    public function testIndexExigeAutenticacao(): void
    {
        $this->get('/auditorias');

        $this->assertNotNull($this->_response);
        $this->assertContains($this->_response->getStatusCode(), [302, 401]);
    }

    /**
     * @return void
     */
    public function testViewExigeAutenticacao(): void
    {
        $this->get('/auditorias/view/1');

        $this->assertNotNull($this->_response);
        $this->assertContains($this->_response->getStatusCode(), [302, 401]);
    }

    /**
     * @return void
     */
    public function testIndexComoRootListaOsRegistros(): void
    {
        $this->criaRegistroAuditoria();

        $this->loginAsUser();
        $this->get('/auditorias');

        $this->assertResponseOk();
        $this->assertResponseContains('Entrada');
        $this->assertResponseContains('Criacao');
        $this->assertResponseContains('usuario.fixture');
    }

    /**
     * A ordenação pelas colunas não pode ficar ambígua com o JOIN em Users (que também
     * tem `created`).
     *
     * @return void
     */
    public function testIndexOrdenadoPorDataFunciona(): void
    {
        $this->criaRegistroAuditoria();

        $this->loginAsUser();
        $this->get('/auditorias?sort=created&direction=asc');

        $this->assertResponseOk();
    }

    /**
     * Detalhe: autor e os nomes dos campos alterados (sem valores).
     *
     * @return void
     */
    public function testViewComoRootMostraAutorECamposAlterados(): void
    {
        $id = $this->criaRegistroAuditoria();

        $this->loginAsUser();
        $this->get('/auditorias/view/' . $id);

        $this->assertResponseOk();
        $this->assertResponseContains('usuario.fixture');
        $this->assertResponseContains('Titulo, Password');
    }

    /**
     * Ação sem login (configuração inicial) e ação de usuário já excluído têm textos
     * diferentes: a trilha guarda o id mesmo depois da exclusão.
     *
     * @return void
     */
    public function testViewIdentificaAcaoSemLoginEUsuarioExcluido(): void
    {
        $semLogin = $this->criaRegistroAuditoria(null);

        $users = $this->fetchTable(UsersTable::class);
        $usuario = $users->saveOrFail($users->newEntity([
            'username' => 'usuario.temporario',
            'email' => 'usuario.temporario@example.com',
            'password' => 'Aa1!' . bin2hex(random_bytes(8)),
        ]));
        $deUsuarioExcluido = $this->criaRegistroAuditoria($usuario->id);
        $users->deleteOrFail($usuario);

        $this->loginAsUser();
        $this->get('/auditorias/view/' . $semLogin);
        $this->assertResponseContains('Sem usuário autenticado');

        $this->get('/auditorias/view/' . $deUsuarioExcluido);
        $this->assertResponseContains('Usuário excluído (#' . $usuario->id . ')');
    }

    /**
     * RN: a Auditoria é aberta a todos os usuários logados, não só a root.
     *
     * @return void
     */
    public function testIndexAcessivelParaUsuarioNaoRoot(): void
    {
        $this->criaRegistroAuditoria();

        $this->loginAsNonRootUser();
        $this->get('/auditorias');

        $this->assertResponseOk();
        $this->assertResponseContains('usuario.fixture');
    }

    /**
     * Um usuário comum vê o detalhe, mas não o IP de origem (dado pessoal de quem fez a ação).
     *
     * @return void
     */
    public function testViewDeNaoRootEscondeOIpDeOrigem(): void
    {
        $id = $this->criaRegistroAuditoria();

        $this->loginAsNonRootUser();
        $this->get('/auditorias/view/' . $id);

        $this->assertResponseOk();
        $this->assertResponseContains('Titulo, Password');
        $this->assertResponseNotContains('IP de origem');
    }

    /**
     * O root continua vendo o IP de origem.
     *
     * @return void
     */
    public function testViewDeRootMostraOIpDeOrigem(): void
    {
        $id = $this->criaRegistroAuditoria();

        $this->loginAsUser();
        $this->get('/auditorias/view/' . $id);

        $this->assertResponseOk();
        $this->assertResponseContains('IP de origem');
    }
}
