<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\LogsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\LogsTable Test Case
 */
class LogsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\LogsTable
     */
    protected $Logs;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.Logs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Logs') ? [] : ['className' => LogsTable::class];
        $this->Logs = $this->getTableLocator()->get('Logs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Logs);

        parent::tearDown();
    }

    /**
     * Um log com todos os campos obrigatórios preenchidos deve ser salvo com sucesso.
     *
     * @return void
     */
    public function testValidationDefaultComDadosValidos(): void
    {
        $log = $this->Logs->newEntity([
            'evento' => 'C2-1',
            'nivel_severidade' => 4,
            'recurso' => '/users',
            'ip_origem' => '198.51.100.20',
            'usuario' => 'usuario@example.com',
            'mensagem' => 'Acesso não autorizado a área restrita.',
        ]);

        $resultado = $this->Logs->save($log);
        $this->assertNotFalse($resultado, 'Failed to save log: ' . json_encode($log->getErrors()));
    }

    /**
     * mensagem e nivel_severidade são obrigatórios; ausentes devem falhar.
     *
     * @return void
     */
    public function testValidationDefaultRejeitaCamposObrigatoriosAusentes(): void
    {
        $semMensagem = $this->Logs->newEntity(['nivel_severidade' => 4]);
        $this->assertFalse($this->Logs->save($semMensagem), 'Should reject missing mensagem');

        $semNivel = $this->Logs->newEntity(['mensagem' => 'Mensagem de teste']);
        $this->assertFalse($this->Logs->save($semNivel), 'Should reject missing nivel_severidade');
    }

    /**
     * ip_origem deve ser um IP válido.
     *
     * @return void
     */
    public function testValidationDefaultRejeitaIpOrigemInvalido(): void
    {
        $ipInvalido = $this->Logs->newEntity([
            'nivel_severidade' => 4,
            'mensagem' => 'Mensagem de teste',
            'ip_origem' => 'nao-e-um-ip',
        ]);

        $this->assertFalse($this->Logs->save($ipInvalido), 'Should reject invalid ip_origem');
    }

    /**
     * findCountAtividadesSuspeitas() deve contar, agrupado por nivel_severidade,
     * apenas os logs não analisados e cujo nível não seja INFO(6)/DEBUG(7)
     * (ver LogsTable::findCountAtividadesSuspeitas()).
     *
     * @return void
     */
    public function testFindCountAtividadesSuspeitasContaApenasNaoAnalisadosRelevantes(): void
    {
        // Da fixture: id=1, nivel_severidade=5 (notice), analisado=0 -> deve contar.
        $this->Logs->save($this->Logs->newEntity([
            'nivel_severidade' => 5,
            'mensagem' => 'Segunda ocorrência não analisada, mesmo nível.',
            'analisado' => false,
        ]));

        // Já analisado -> não deve contar.
        $this->Logs->save($this->Logs->newEntity([
            'nivel_severidade' => 3,
            'mensagem' => 'Ocorrência já analisada.',
            'analisado' => true,
        ]));

        // Nível INFO (6) -> excluído mesmo não analisado.
        $this->Logs->save($this->Logs->newEntity([
            'nivel_severidade' => 6,
            'mensagem' => 'Ocorrência informativa, não suspeita.',
            'analisado' => false,
        ]));

        $resultado = $this->Logs->find('countAtividadesSuspeitas')->toArray();
        $porNivel = [];
        foreach ($resultado as $linha) {
            $porNivel[$linha->nivel_severidade] = $linha->quantidade;
        }

        $this->assertSame(2, $porNivel[5] ?? null, 'Deveria contar as 2 ocorrências de nível 5 não analisadas.');
        $this->assertArrayNotHasKey(3, $porNivel, 'Ocorrências já analisadas não deveriam ser contadas.');
        $this->assertArrayNotHasKey(6, $porNivel, 'Nível INFO (6) deveria ser excluído da contagem.');
    }
}
