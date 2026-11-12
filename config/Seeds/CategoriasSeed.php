<?php
declare(strict_types=1);

use App\Criptografia\Criptografia;
use Migrations\BaseSeed;

/**
 * Categorias de exemplo, para o ambiente de desenvolvimento: `bin/cake seeds run CategoriasSeed`.
 *
 * O nome vai cifrado, como o Categoria::_setNome() grava (o insert do seed não passa pela entidade).
 */
class CategoriasSeed extends BaseSeed
{
    /**
     * @return void
     */
    public function run(): void
    {
        $agora = date('Y-m-d H:i:s');
        $nomes = [
            'Redes sociais / E-mails / Serviços',
            'Infraestrutura',
            'Serviços de Infra e Programação',
            'Máquinas e Equipamentos',
            'Faculdade / Tecnologia',
            'Bancos / Dinheiro',
            'Jogos',
            'Backup',
            'Trabalhos',
            'Outros',
            'Carros',
            'Serviço na Nuvem',
        ];

        $data = [];
        foreach ($nomes as $posicao => $nome) {
            $data[] = [
                'nome' => Criptografia::criptografar($nome),
                'posicao' => $posicao + 1,
                'created' => $agora,
                'modified' => $agora,
            ];
        }

        $this->table('categorias')->insert($data)->save();
    }
}
