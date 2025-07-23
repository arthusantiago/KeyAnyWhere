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
     * @var array<string>
     */
    protected array $fixtures = [
        'app.IpsBloqueados',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
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
    protected function tearDown(): void
    {
        unset($this->IpsBloqueados);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\IpsBloqueadosTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
