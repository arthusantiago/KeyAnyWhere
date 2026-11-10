<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateConfiguracoes extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('configuracoes');
        $table->addColumn('chave', 'string', ['null' => false]);
        $table->addColumn('valor', 'string', ['default' => '', 'limit' => 100]);
        $table->addColumn('created', 'datetime');
        $table->addColumn('modified', 'datetime');
        $table->addIndex('chave', ['unique' => true]);
        $table->create();
    }
}
