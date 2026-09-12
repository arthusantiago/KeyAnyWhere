<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Entrada;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Entrada Test Case
 *
 * Cobre os mutators/descriptografadores de criptografia (ver App\Criptografia\Criptografia)
 * usados por 'titulo', 'username', 'password', 'link' e 'anotacoes' — a parte mais sensível
 * do sistema (o cofre de senhas), e que antes não tinha nenhum teste direto.
 */
class EntradaTest extends TestCase
{
    /**
     * Gera um valor aleatório para uso como senha nos testes (não é um segredo real,
     * apenas evita ter no código-fonte uma string estática com "cara" de senha).
     *
     * @return string
     */
    private static function gerarSenhaDeTeste(): string
    {
        return 'Aa1!' . bin2hex(random_bytes(8));
    }

    /**
     * O texto puro definido em cada campo deve ser criptografado ao ser atribuído (mutator),
     * e o campo bruto da entidade não deve conter o texto puro.
     *
     * @return void
     */
    public function testCamposSaoCriptografadosAoSeremAtribuidos(): void
    {
        $senha = self::gerarSenhaDeTeste();
        $entrada = new Entrada([
            'titulo' => 'Conta do banco',
            'username' => 'meu.usuario',
            'password' => $senha,
            'link' => 'https://banco.exemplo.com',
            'anotacoes' => 'Anotação sensível',
        ]);

        $this->assertNotSame('Conta do banco', $entrada->titulo);
        $this->assertNotSame('meu.usuario', $entrada->username);
        $this->assertNotSame($senha, $entrada->password);
        $this->assertNotSame('https://banco.exemplo.com', $entrada->link);
        $this->assertNotSame('Anotação sensível', $entrada->anotacoes);
    }

    /**
     * Cada campo criptografado deve retornar o texto puro original através do
     * método *Descrip() correspondente (round-trip de criptografia/descriptografia).
     *
     * @return void
     */
    public function testCamposSaoDescriptografadosCorretamente(): void
    {
        $senha = self::gerarSenhaDeTeste();
        $entrada = new Entrada([
            'titulo' => 'Conta do banco',
            'username' => 'meu.usuario',
            'password' => $senha,
            'link' => 'https://banco.exemplo.com',
            'anotacoes' => 'Anotação sensível',
        ]);

        $this->assertSame('Conta do banco', $entrada->tituloDescrip());
        $this->assertSame('meu.usuario', $entrada->usernameDescrip());
        $this->assertSame($senha, $entrada->passwordDescrip());
        $this->assertSame('https://banco.exemplo.com', $entrada->linkDescrip());
        $this->assertSame('Anotação sensível', $entrada->anotacoesDescrip());
    }

    /**
     * linkEncurtado() deve descriptografar e truncar o link no tamanho informado.
     *
     * @return void
     */
    public function testLinkEncurtadoTruncaOLinkDescriptografado(): void
    {
        $entrada = new Entrada([
            'link' => 'https://banco.exemplo.com/pagina/muito/longa/de/verdade',
        ]);

        $this->assertSame(
            'https://banco.exemplo.com/p',
            $entrada->linkEncurtado(27),
        );
    }

    /**
     * Duas criptografias do mesmo texto puro devem gerar ciphertexts diferentes
     * (nonce aleatório a cada chamada — ver Criptografia::criptografar()).
     *
     * @return void
     */
    public function testCriptografiaUsaNonceAleatorio(): void
    {
        $entrada1 = new Entrada(['titulo' => 'Mesma senha']);
        $entrada2 = new Entrada(['titulo' => 'Mesma senha']);

        $this->assertNotSame($entrada1->titulo, $entrada2->titulo);
        $this->assertSame($entrada1->tituloDescrip(), $entrada2->tituloDescrip());
    }
}
