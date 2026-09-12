<?php
declare(strict_types=1);

namespace App\Test\Fixture;

/**
 * IpsBloqueadosFixture
 */
class IpsBloqueadosFixture extends AppFixture
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
                'ip' => '203.0.113.10',
                'created' => 1685370299,
                'modified' => 1685370299,
            ],
        ];
        parent::init();
    }
}
