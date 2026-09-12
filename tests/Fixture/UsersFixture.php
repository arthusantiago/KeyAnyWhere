<?php
declare(strict_types=1);

namespace App\Test\Fixture;

/**
 * UsersFixture
 */
class UsersFixture extends AppFixture
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
                'username' => 'usuario.fixture',
                'email' => 'usuario.fixture@example.com',
                'password' => password_hash('SenhaFixture123!@#', PASSWORD_BCRYPT),
                'tfa_secret' => 'Lorem ipsum dolor sit amet',
                'tfa_ativo' => 1,
                'root' => 1,
                'created' => 1643082492,
                'modified' => 1643082492,
            ],
        ];
        parent::init();
    }
}
