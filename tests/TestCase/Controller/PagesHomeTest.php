<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Table\AuditoriasTable;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Home ("Minha Segurança"): visível para todo usuário logado.
 *
 * @uses \App\Controller\PagesController::home()
 */
class PagesHomeTest extends TestCase
{
    use IntegrationTestTrait;
    use AuthenticatedTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Sessions',
        'app.Auditorias',
    ];

    /**
     * @return void
     */
    private function criaRegistroAuditoria(): void
    {
        $auditorias = $this->fetchTable(AuditoriasTable::class);
        $auditorias->saveOrFail($auditorias->newEntity([
            'contexto' => 'entrada',
            'id_entidade' => 1,
            'acao' => 'criacao',
            'usuario_id' => 1,
            'alteracoes' => ['titulo' => ['antes' => '', 'depois' => '']],
        ]));
    }

    /**
     * RN (D16): todos os usuários veem as últimas alterações da Auditoria, com o link para a lista
     * completa e para o detalhe.
     *
     * @return void
     */
    public function testUsuarioComumVeUltimasAlteracoesComLinks(): void
    {
        $this->criaRegistroAuditoria();

        $this->loginAsNonRootUser();
        $this->get('/');

        $this->assertResponseOk();
        $this->assertResponseContains('Últimas alterações');
        $this->assertResponseContains('Entrada (criacao) por usuario.fixture');
        $this->assertResponseContains('/auditorias/view/');
        $this->assertResponseContains('Lista completa');
    }

    /**
     * Sem registros, a home diz isso em vez de uma lista vazia.
     *
     * @return void
     */
    public function testSemAlteracoesMostraAviso(): void
    {
        $this->loginAsUser();
        $this->get('/');

        $this->assertResponseOk();
        $this->assertResponseContains('Nenhuma alteração registrada ainda.');
    }

    /**
     * A home não mostra mais as "Atividades suspeitas" (sistema de eventos removido, D14) nem os IPs
     * bloqueados (funcionalidade removida).
     *
     * @return void
     */
    public function testHomeSemAtividadesSuspeitasNemIpsBloqueados(): void
    {
        $this->loginAsUser();
        $this->get('/');

        $this->assertResponseOk();
        $this->assertResponseNotContains('Atividades suspeitas');
        $this->assertResponseNotContains('IPs bloqueados');
        $this->assertResponseNotContains('/logs');
    }
}
