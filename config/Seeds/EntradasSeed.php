<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * Entradas seed.
 */
class EntradasSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run(): void
    {
        //Obtendo as categtorias cadastradas
        $categorias = $this->fetchAll('SELECT * FROM categorias');

        $data = [];

        foreach($categorias as $categoria)
        {
            for ($i = 0; $i < 20; $i++)
            {
                $data[] = [
                    'titulo' => 'REFAZER-ESSE-SEED',
                    'username' =>'REFAZER-ESSE-SEED@REFAZER.COM',
                    'password' => sha1('REFAZER-ESSE-SEED'),
                    'link' =>  'https://' . 'REFAZER-ESSE-SEED',
                    'anotacoes' => 'Ut ab voluptas sed a nam. Sint autem inventore aut officia aut aut blanditiis. Ducimus eos odit amet et est ut eum.',
                    'categoria_id' => $categoria['id'],
                    'created' => date('Y-m-d H:i:s'),
                    'modified' => date('Y-m-d H:i:s')
                ];
            }
        }

        $table = $this->table('entradas');
        $table->insert($data)->save();
    }
}
