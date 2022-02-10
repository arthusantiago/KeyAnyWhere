<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * Subcategorias seed.
 */
class SubcategoriasSeed extends AbstractSeed
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
    public function run()
    {
        $dateTimeCorrente = date('Y-m-d H:i:s');
        
        $data = [
            [
                'nome'    => 'Redes Sociais',
                'categoria_id' => 1,
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Clouds',
                'categoria_id' => 2,
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Estudo',
                'categoria_id' => 3,
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Equipamentos de Rede',
                'categoria_id' => 4,
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Serviços da Faculdade',
                'categoria_id' => 5,
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Corretores de Valores',
                'categoria_id' => 6,
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Jogos de Guerra',
                'categoria_id' => 7,
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ]
        ];

        $table = $this->table('subcategorias');
        $table->insert($data)->save();
    }
}
