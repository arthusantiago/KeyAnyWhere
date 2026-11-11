<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SessionsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SessionsTable Test Case
 */
class SessionsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SessionsTable
     */
    protected $Sessions;

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
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Sessions') ? [] : ['className' => SessionsTable::class];
        $this->Sessions = $this->getTableLocator()->get('Sessions', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Sessions);

        parent::tearDown();
    }

    /**
     * Uma sessão com um user_id existente deve ser salva com sucesso.
     *
     * @return void
     */
    public function testValidationDefaultComDadosValidos(): void
    {
        $sessao = $this->Sessions->newEntity([
            'user_id' => 1,
            'user_agent' => 'Mozilla/5.0',
        ]);
        // 'id' não é mass-assignable (guarded na entidade, pois normalmente é
        // definido pelo framework de sessão do PHP) — atribuído diretamente.
        $sessao->id = 'sessao-de-teste';

        $resultado = $this->Sessions->save($sessao);
        $this->assertNotFalse($resultado, 'Failed to save sessao: ' . json_encode($sessao->getErrors()));
    }

    /**
     * user_agent excedendo 256 caracteres deve falhar.
     *
     * @return void
     */
    public function testValidationDefaultRejeitaUserAgentMuitoLongo(): void
    {
        $sessao = $this->Sessions->newEntity([
            'user_id' => 1,
            'user_agent' => str_repeat('a', 257),
        ]);
        $sessao->id = 'sessao-agent-longo';

        $this->assertFalse($this->Sessions->save($sessao), 'Should reject user_agent exceeding 256 chars');
        $this->assertArrayHasKey('user_agent', $sessao->getErrors());
    }

    /**
     * Test buildRules method - IMPORTANT VALIDATION TEST
     * Validates foreign key constraint on user_id
     *
     * @return void
     * @uses \App\Model\Table\SessionsTable::buildRules()
     */
    public function testBuildRulesRejeitaUserIdInexistente(): void
    {
        $sessao = $this->Sessions->newEntity([
            'user_id' => 9999,
        ]);
        $sessao->id = 'sessao-user-invalido';

        $this->assertFalse($this->Sessions->save($sessao), 'Should enforce foreign key constraint on user_id');
        $this->assertArrayHasKey('user_id', $sessao->getErrors());
    }

    /**
     * O validador do finalizarSessao exige um UUID no id da sessão.
     *
     * @return void
     * @uses \App\Model\Table\SessionsTable::validationIdSession()
     */
    public function testValidationIdSession(): void
    {
        $validador = $this->Sessions->getValidator('idSession');

        $this->assertSame([], $validador->validate(['id' => '0b7d7e6e-6f4b-4c3e-9a51-2f3c4d5e6f70'], false));
        $this->assertArrayHasKey('uuid', $validador->validate(['id' => 'nao-e-um-uuid'], false)['id']);
        $this->assertArrayHasKey('_empty', $validador->validate(['id' => ''], false)['id']);
    }
}
