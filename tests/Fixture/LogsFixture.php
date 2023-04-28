<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * LogsFixture
 */
class LogsFixture extends TestFixture
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
                'nivel_severidade' => 1,
                'recurso' => 'Lorem ipsum dolor sit amet',
                'ip_origem' => 'Lorem ipsum dolor sit amet',
                'usuario' => 'Lorem ipsum dolor sit amet',
                'mensagem' => 'Lorem ipsum dolor sit amet',
                'created' => 1682470090,
                'modified' => 1682470090,
            ],
        ];
        parent::init();
    }
}
