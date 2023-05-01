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
        ->addColumn('evento', 'string', ['limit' => 7, 'null' => true, 'default' => null])
        ->addColumn('nivel_severidade', 'integer', ['null' => false, 'default' => null])
        ->addColumn('recurso', 'string', ['limit' => 100, 'null' => true, 'default' => null])
        ->addColumn('ip_origem', 'string', ['limit' => 39, 'null' => true, 'default' => null])
        ->addColumn('usuario', 'string', ['limit' => 200, 'null' => true, 'default' => null])
        ->addColumn('mensagem', 'string', ['limit' => 256, 'null' => false])
        ->addColumn('created', 'datetime')
        ->addColumn('modified', 'datetime')
        ->create();
    }
}
