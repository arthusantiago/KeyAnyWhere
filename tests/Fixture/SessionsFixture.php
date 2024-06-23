<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SessionsFixture
 */
class SessionsFixture extends TestFixture
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
                'id' => 'a73c9ad0-5b34-4203-943e-4063a535e863',
                'data' => 'Lorem ipsum dolor sit amet',
                'expires' => 1,
                'user_id' => 1,
                'created' => 1719084055,
                'modified' => 1719084055,
            ],
        ];
        parent::init();
    }
}
