<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Criptografia\Criptografia;

/**
 * CategoriasFixture
 *
 * 'nome' é tratado pela aplicação como ciphertext (ver mutator em
 * App\Model\Entity\Categoria) — precisa ser gerado com Criptografia::criptografar()
 * para que qualquer teste que leia esse campo via getter consiga descriptografar.
 */
class CategoriasFixture extends AppFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'nome' => Criptografia::criptografar('Categoria de Teste'),
                'posicao' => 1,
                'created' => 1643247037,
                'modified' => 1643247037,
            ],
        ];
        parent::init();
    }
}
