<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * InsecurePasswordsFixture
 */
class InsecurePasswordsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'password' => 'Lorem ipsum dolor sit amet',
                'created' => 1688597054,
                'modified' => 1688597054,
            ],
        ];
        parent::init();
    }
}
