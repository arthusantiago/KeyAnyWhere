<?php
declare(strict_types=1);

use Migrations\BaseMigration;
use Cake\Database\Expression\QueryExpression;

class CreateSessao extends BaseMigration
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
        $adaptador = $this->adapter->getAdapterType();

        // Only create uuid-ossp extension for PostgreSQL
        // SQLite doesn't support this extension
        if ($adaptador === 'pgsql') {
            $this->execute('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
        }

        $table = $this->table('sessions', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'string', ['limit' => 40, 'null' => false]);

        // id_secundario: UUID with different defaults for different databases
        if ($adaptador === 'pgsql') {
            $table->addColumn('id_secundario', 'uuid', ['default' => new QueryExpression('uuid_generate_v4()') ]);
        } else {
            // For SQLite and other databases, use a regular string and generate in PHP
            $table->addColumn('id_secundario', 'string', ['limit' => 40, 'null' => false]);
        }

        $table
            ->addColumn('data', 'binary', ['null' => true, 'default' => null])
            ->addColumn('expires', 'integer', ['null' => true, 'default' => null])
            ->addColumn('user_id', 'integer', ['null' => true, 'default' => null])
            ->addColumn('user_agent', 'string', ['limit' => 256, 'null' => true, 'default' => null])
            ->addColumn('created', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('modified', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->create();

        $this->table('sessions')
            ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
            ->save();
    }
}
