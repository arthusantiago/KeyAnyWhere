<?php
declare(strict_types=1);

use App\Criptografia\Criptografia;
use Migrations\BaseSeed;

/**
 * Entradas de exemplo, 20 por categoria, para o ambiente de desenvolvimento (rode o CategoriasSeed
 * antes): `bin/cake seeds run EntradasSeed`.
 *
 * Título, usuário, senha, link e anotações vão cifrados, como os mutators da Entrada gravam (o insert do
 * seed não passa pela entidade).
 */
class EntradasSeed extends BaseSeed
{
    /**
     * @return void
     */
    public function run(): void
    {
        $agora = date('Y-m-d H:i:s');
        $data = [];
        foreach ($this->fetchAll('SELECT id FROM categorias ORDER BY id') as $categoria) {
            for ($i = 1; $i <= 20; $i++) {
                $data[] = [
                    'titulo' => Criptografia::criptografar('Exemplo ' . $i),
                    'username' => Criptografia::criptografar('exemplo' . $i . '@example.com'),
                    'password' => Criptografia::criptografar(bin2hex(random_bytes(8))),
                    'link' => Criptografia::criptografar('https://example.com/' . $i),
                    'anotacoes' => Criptografia::criptografar('Entrada de exemplo gerada pelo EntradasSeed.'),
                    'categoria_id' => $categoria['id'],
                    'posicao' => $i,
                    'created' => $agora,
                    'modified' => $agora,
                ];
            }
        }

        $this->table('entradas')->insert($data)->save();
    }
}
