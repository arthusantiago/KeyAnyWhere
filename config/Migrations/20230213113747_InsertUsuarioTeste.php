<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class InsertUsuarioTeste extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $builder = $this->getQueryBuilder();
        $dateTime = date('Y-m-d H:i:s');

        $builder
            ->insert([
                'username',
                'email',
                'password',
                'created',
                'modified'
            ])
            ->into('users')
            ->values([
                'username' => 'User Teste',
                'email' => 'teste@teste.com',
                'password' => '$2y$10$0SeMz3/0NQTHbsCoqsFKa.y9hGAliYjJSyI8c1VA24N0bpBVuAjVa', // senha: qwe123@!
                'created' => $dateTime,
                'modified' => $dateTime
            ])
            ->execute();
    }
}
