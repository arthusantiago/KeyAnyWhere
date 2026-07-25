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
                'id' => '7a1fd818-c931-45a6-bb53-b01ba613781e',
                'data' => 'Lorem ipsum dolor sit amet',
                'expires' => 1,
                'user_id' => 1,
                'userAgent' => 'Lorem ipsum dolor sit amet',
                'created' => 1720745290,
                'modified' => 1720745290,
            ],
        ];
        parent::init();
    }
}
