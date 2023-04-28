<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateTabelaLogs extends AbstractMigration
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
        $this->table('logs')
        ->addColumn('nivel_severidade', 'integer', ['null' => false])
        ->addColumn('recurso', 'string', ['limit' => 100, 'null' => false,])
        ->addColumn('ip_origem', 'string', ['limit' => 39, 'null' => false])
        ->addColumn('usuario', 'string', ['limit' => 200, 'null' => true, 'default' => null])
        ->addColumn('mensagem', 'string', ['limit' => 256])
        ->addColumn('created', 'datetime')
        ->addColumn('modified', 'datetime')
        ->create();
    }
}
