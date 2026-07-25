<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Phinx\Util\Literal;

class CreateSessao extends AbstractMigration
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
        $this->execute('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');

        $this->table('sessions', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'string', ['limit' => 40, 'null' => false])
            ->addColumn('id_secundario', 'uuid', ['default' => Literal::from('uuid_generate_v4()')])
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
