<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SubcategoriasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SubcategoriasTable Test Case
 */
class SubcategoriasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SubcategoriasTable
     */
    protected $Subcategorias;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Subcategorias',
        'app.Categorias',
        'app.Entradas',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Subcategorias') ? [] : ['className' => SubcategoriasTable::class];
        $this->Subcategorias = $this->getTableLocator()->get('Subcategorias', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Subcategorias);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\SubcategoriasTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\SubcategoriasTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
