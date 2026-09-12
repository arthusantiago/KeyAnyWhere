<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Categoria;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Categoria Test Case
 *
 * Cobre o mutator/descriptografador de criptografia (ver App\Criptografia\Criptografia)
 * usado pelo campo 'nome' — antes sem nenhum teste direto.
 */
class CategoriaTest extends TestCase
{
    /**
     * O texto puro definido em 'nome' deve ser criptografado ao ser atribuído (mutator),
     * e o campo bruto da entidade não deve conter o texto puro.
     *
     * @return void
     */
    public function testNomeECriptografadoAoSerAtribuido(): void
    {
        $categoria = new Categoria(['nome' => 'Bancos']);

        $this->assertNotSame('Bancos', $categoria->nome);
    }

    /**
     * nomeDescrip() deve retornar o texto puro original (round-trip de
     * criptografia/descriptografia).
     *
     * @return void
     */
    public function testNomeEDescriptografadoCorretamente(): void
    {
        $categoria = new Categoria(['nome' => 'Bancos']);

        $this->assertSame('Bancos', $categoria->nomeDescrip());
    }

    /**
     * nomeEncurtado() não deve truncar nomes dentro do limite.
     *
     * @return void
     */
    public function testNomeEncurtadoNaoTruncaNomeDentroDoLimite(): void
    {
        $categoria = new Categoria(['nome' => 'Bancos']);

        $this->assertSame('Bancos', $categoria->nomeEncurtado(35));
    }

    /**
     * nomeEncurtado() deve truncar e adicionar reticências em nomes que excedem o limite.
     *
     * @return void
     */
    public function testNomeEncurtadoTruncaNomeQueExcedeOLimite(): void
    {
        $categoria = new Categoria(['nome' => str_repeat('a', 40)]);

        $this->assertSame(str_repeat('a', 35) . ' ...', $categoria->nomeEncurtado(35));
    }

    /**
     * Duas criptografias do mesmo texto puro devem gerar ciphertexts diferentes
     * (nonce aleatório a cada chamada — ver Criptografia::criptografar()).
     *
     * @return void
     */
    public function testCriptografiaUsaNonceAleatorio(): void
    {
        $categoria1 = new Categoria(['nome' => 'Mesmo nome']);
        $categoria2 = new Categoria(['nome' => 'Mesmo nome']);

        $this->assertNotSame($categoria1->nome, $categoria2->nome);
        $this->assertSame($categoria1->nomeDescrip(), $categoria2->nomeDescrip());
    }
}
