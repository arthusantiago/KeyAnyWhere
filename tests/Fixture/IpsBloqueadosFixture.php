<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * IpsBloqueadosFixture
 */
class IpsBloqueadosFixture extends TestFixture
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
                'ip' => 'Lorem ipsu',
                'created' => 1685370299,
                'modified' => 1685370299,
            ],
        ];
        parent::init();
    }
}
