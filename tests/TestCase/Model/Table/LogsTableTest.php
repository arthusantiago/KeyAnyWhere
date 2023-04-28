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
     * @var array<string>
     */
    protected $fixtures = [
        'app.Logs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
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
    protected function tearDown(): void
    {
        unset($this->Logs);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\LogsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
