<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Criptografia\Criptografia;

/**
 * EntradasFixture
 *
 * Os campos abaixo são tratados pela aplicação como ciphertext (ver mutators
 * em App\Model\Entity\Entrada) — precisam ser gerados com Criptografia::criptografar()
 * para que qualquer teste que leia esses campos via getter consiga descriptografar.
 */
class EntradasFixture extends AppFixture
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
                'titulo' => Criptografia::criptografar('Entrada de Teste'),
                'username' => Criptografia::criptografar('usuario.teste'),
                'password' => Criptografia::criptografar('SenhaSuperSecreta123!'),
                'link' => Criptografia::criptografar('https://exemplo.com'),
                'anotacoes' => Criptografia::criptografar('Anotações de teste para a entrada.'),
                'categoria_id' => 1,
                'created' => 1643082487,
                'modified' => 1643082487,
            ],
        ];
        parent::init();
    }
}
