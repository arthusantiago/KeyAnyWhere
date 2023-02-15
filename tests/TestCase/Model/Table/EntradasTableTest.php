<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EntradasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EntradasTable Test Case
 */
class EntradasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\EntradasTable
     */
    protected $Entradas;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Entradas',
        'app.Categorias',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Entradas') ? [] : ['className' => EntradasTable::class];
        $this->Entradas = $this->getTableLocator()->get('Entradas', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Entradas);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\EntradasTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\EntradasTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
