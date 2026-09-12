<?php
declare(strict_types=1);

namespace App\Test\Fixture;

/**
 * LogsFixture
 */
class LogsFixture extends AppFixture
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
                'evento' => 'C1-1',
                'nivel_severidade' => 5,
                'recurso' => '/users/login',
                'ip_origem' => '203.0.113.10',
                'usuario' => 'usuario.teste@example.com',
                'mensagem' => 'Durante o login o usuário errou o user ou password.',
                'analisado' => 0,
                'created' => 1682944824,
                'modified' => 1682944824,
            ],
        ];
        parent::init();
    }
}
