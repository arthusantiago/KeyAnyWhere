<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateIpsBloqueados extends AbstractMigration
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
        $this->table('ips_bloqueados')
        ->addColumn('ip', 'string', ['limit' => 15, 'null' => false])
        ->addColumn('created', 'datetime')
        ->addColumn('modified', 'datetime')
        ->create();
    }
}
