<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Inicial extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $this->table('categorias')
        ->addColumn('nome', 'string', ['limit' => 100, 'null' => false])
        ->addColumn('created', 'datetime')
        ->addColumn('modified', 'datetime')
        ->create();

        $this->table('subcategorias')
        ->addColumn('nome', 'string', ['limit' => 100, 'null' => false])
        ->addColumn('categoria_id', 'integer', ['default' => null, 'null' => true])
        ->addColumn('created', 'datetime')
        ->addColumn('modified', 'datetime')
        ->create();

        $this->table('subcategorias')
        ->addForeignKey('categoria_id', 'categorias', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
        ->save();

        $this->table('users')
        ->addColumn('username', 'string', ['limit' => 50, 'null' => false])
        ->addColumn('email', 'string', ['limit' => 100, 'null' => false])
        ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
        ->addColumn('created', 'datetime')
        ->addColumn('modified', 'datetime')
        ->create();        

        $this->table('entradas')
        ->addColumn('titulo', 'string', ['limit' => 100, 'null' => false])
        ->addColumn('username', 'string', ['limit' => 100, 'null' => false])
        ->addColumn('password', 'string', ['limit' => 256, 'null' => false])
        ->addColumn('link', 'string', ['limit' => 500, 'default' => null, 'null' => true])
        ->addColumn('anotacoes', 'text')
        ->addColumn('categoria_id', 'integer')
        ->addColumn('subcategoria_id', 'integer', ['null' => true, 'default' => null])
        ->addColumn('user_id', 'integer')
        ->addColumn('created', 'datetime')
        ->addColumn('modified', 'datetime')
        ->create();

        $this->table('entradas')
        ->addForeignKey('categoria_id', 'categorias', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
        ->save();

        $this->table('entradas')
        ->addForeignKey('subcategoria_id', 'subcategorias', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
        ->save();

        $this->table('entradas')
        ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
        ->save();
    }
}
