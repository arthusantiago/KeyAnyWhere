<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Remove a tabela `logs`, do antigo catálogo de eventos: os eventos de segurança agora ficam na
 * tabela `eventos_seguranca` (e no arquivo `logs/seguranca.log`).
 */
class RemoveLogs extends BaseMigration
{
    /**
     * @return void
     */
    public function up(): void
    {
        $this->table('logs')->drop()->save();
    }

    /**
     * Recria a tabela vazia, como na migration inicial (os registros não voltam).
     *
     * @return void
     */
    public function down(): void
    {
        $this->table('logs')
            ->addColumn('evento', 'string', ['limit' => 7, 'null' => true, 'default' => null])
            ->addColumn('nivel_severidade', 'integer', ['null' => false, 'default' => null])
            ->addColumn('recurso', 'string', ['limit' => 100, 'null' => true, 'default' => null])
            ->addColumn('ip_origem', 'string', ['limit' => 39, 'null' => true, 'default' => null])
            ->addColumn('usuario', 'string', ['limit' => 200, 'null' => true, 'default' => null])
            ->addColumn('mensagem', 'string', ['limit' => 256, 'null' => false])
            ->addColumn('analisado', 'boolean', ['default' => false])
            ->addColumn('created', 'datetime')
            ->addColumn('modified', 'datetime')
            ->addIndex(['nivel_severidade', 'analisado'])
            ->create();
    }
}
