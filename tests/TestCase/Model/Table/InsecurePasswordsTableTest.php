<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\InsecurePasswordsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\InsecurePasswordsTable Test Case
 */
class InsecurePasswordsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\InsecurePasswordsTable
     */
    protected $InsecurePasswords;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.InsecurePasswords',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('InsecurePasswords') ? [] : ['className' => InsecurePasswordsTable::class];
        $this->InsecurePasswords = $this->getTableLocator()->get('InsecurePasswords', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->InsecurePasswords);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\InsecurePasswordsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
