<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\IpsBloqueadosTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\IpsBloqueadosTable Test Case
 */
class IpsBloqueadosTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\IpsBloqueadosTable
     */
    protected $IpsBloqueados;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.IpsBloqueados',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('IpsBloqueados') ? [] : ['className' => IpsBloqueadosTable::class];
        $this->IpsBloqueados = $this->getTableLocator()->get('IpsBloqueados', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->IpsBloqueados);

        parent::tearDown();
    }

    /**
     * Um IP válido deve ser salvo com sucesso.
     *
     * @return void
     */
    public function testValidationDefaultComIpValido(): void
    {
        $ipBloqueado = $this->IpsBloqueados->newEntity(['ip' => '198.51.100.30']);

        $resultado = $this->IpsBloqueados->save($ipBloqueado);
        $this->assertNotFalse($resultado, 'Failed to save IP bloqueado: ' . json_encode($ipBloqueado->getErrors()));
    }

    /**
     * IP ausente ou em formato inválido deve falhar.
     *
     * @return void
     */
    public function testValidationDefaultRejeitaIpInvalidoOuAusente(): void
    {
        $semIp = $this->IpsBloqueados->newEntity([]);
        $this->assertFalse($this->IpsBloqueados->save($semIp), 'Should reject missing ip');

        $ipInvalido = $this->IpsBloqueados->newEntity(['ip' => 'nao-e-um-ip']);
        $this->assertFalse($this->IpsBloqueados->save($ipInvalido), 'Should reject invalid ip format');
    }

    /**
     * buildRules deve impedir dois registros com o mesmo IP.
     *
     * @return void
     */
    public function testBuildRulesRejeitaIpDuplicado(): void
    {
        // '203.0.113.10' já existe na fixture.
        $duplicado = $this->IpsBloqueados->newEntity(['ip' => '203.0.113.10']);

        $this->assertFalse($this->IpsBloqueados->save($duplicado), 'Should enforce unique ip constraint');
    }

    /**
     * Atualizar um IP bloqueado existente deve persistir a alteração no banco.
     *
     * @return void
     */
    public function testAtualizarIpBloqueadoPersisteAlteracoes(): void
    {
        $ipBloqueado = $this->IpsBloqueados->get(1);
        $ipBloqueado = $this->IpsBloqueados->patchEntity($ipBloqueado, ['ip' => '198.51.100.40']);
        $resultado = $this->IpsBloqueados->save($ipBloqueado);
        $this->assertNotFalse($resultado, 'Failed to update IP bloqueado: ' . json_encode($ipBloqueado->getErrors()));

        $recarregado = $this->IpsBloqueados->get(1);
        $this->assertSame('198.51.100.40', $recarregado->ip);
    }

    /**
     * Excluir um IP bloqueado existente deve removê-lo do banco (desbloqueio manual).
     *
     * @return void
     */
    public function testExcluirIpBloqueadoRemoveDoBanco(): void
    {
        $ipBloqueado = $this->IpsBloqueados->get(1);

        $resultado = $this->IpsBloqueados->delete($ipBloqueado);
        $this->assertTrue($resultado, 'Failed to delete IP bloqueado.');
        $this->assertFalse($this->IpsBloqueados->exists(['id' => 1]));
    }

    /**
     * findUltimosBloqueados() deve retornar apenas ip/created, ordenado do mais recente
     * para o mais antigo, limitado a 7 registros.
     *
     * @return void
     */
    public function testFindUltimosBloqueadosOrdenaDoMaisRecenteELimitaA7(): void
    {
        for ($i = 0; $i < 8; $i++) {
            $this->IpsBloqueados->save($this->IpsBloqueados->newEntity(['ip' => "198.51.100.{$i}"]));
        }

        $resultados = $this->IpsBloqueados->find('ultimosBloqueados')->toArray();

        $this->assertLessThanOrEqual(7, count($resultados));
        $this->assertGreaterThanOrEqual(
            $resultados[count($resultados) - 1]->created,
            $resultados[0]->created,
        );
    }
}
