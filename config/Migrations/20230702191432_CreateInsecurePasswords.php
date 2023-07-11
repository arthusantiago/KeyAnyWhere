<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateInsecurePasswords extends AbstractMigration
{
    /**
     * Criando a tabela que armazena as senhas inseguras catalogadas na internet.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     * @see https://github.com/danielmiessler/SecLists/tree/master/Passwords/Common-Credentials
     */
    public function change(): void
    {
        $this->table('insecure_passwords')
        ->addColumn('password', 'string', ['limit' => 100, 'null' => false])
        ->addIndex(['password'])
        ->addColumn('created', 'datetime')
        ->addColumn('modified', 'datetime')
        ->create();
    }
}
