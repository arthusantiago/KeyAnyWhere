<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Auditorias extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $this->table('auditorias')
            ->addColumn('contexto', 'string', ['limit' => 100])
            ->addColumn('id_entidade', 'integer')
            ->addColumn('acao', 'string', ['limit' => 50])
            ->addColumn('usuario_id', 'integer')
            ->addColumn('alteracoes', 'jsonb', ['null' => true])
            ->addColumn('ip_origem', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('usuario_id', 'users', 'id', ['delete' => 'RESTRICT', 'update' => 'RESTRICT', 'name' => 'fk_auditorias_usuario_id'])
            ->addIndex(['contexto', 'id_entidade'], ['name' => 'idx_auditorias_contexto_entidade'])
            ->addIndex(['usuario_id'], ['name' => 'idx_auditorias_usuario_id'])
            ->create();
    }
}
