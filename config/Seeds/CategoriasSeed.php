<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * Categorias seed.
 */
class CategoriasSeed extends AbstractSeed
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
        $dateTimeCorrente = date('Y-m-d H:i:s');

        $data = [
            [
                'nome'    => 'Redes socias / E-mails / Serviços',
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Infraestrutura',
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Serviços de Infra e Programação',
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Maquinas e Equipamentos',
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Faculdade/ Tecnologia',
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Bancos/Dinheiro',
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Jogos',
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Backup',
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Trabalhos',
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Outros',
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Carros',
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ],[
                'nome'    => 'Serviço na Nuvem',
                'created' => $dateTimeCorrente,
                'modified' => $dateTimeCorrente
            ]
        ];

        $table = $this->table('categorias');
        $table->insert($data)->save();

    }
}
