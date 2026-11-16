<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\User;
use App\Service\DadosCadastro2fa;
use Cake\TestSuite\TestCase;

class DadosCadastro2faTest extends TestCase
{
    /**
     * A chave sai agrupada de 4 em 4, o codinome leva o emissor e o e-mail, e o QrCode é um SVG do
     * tamanho pedido com a URL `otpauth://` da conta.
     *
     * @return void
     */
    public function testDoUsuario(): void
    {
        $user = new User();
        $user->email = 'pessoa@example.com';
        $user->tfa_secret = 'ABCDEFGHIJKLMNOP';

        $dados = DadosCadastro2fa::doUsuario($user, 321);

        $this->assertSame('ABCD EFGH IJKL MNOP', $dados['chave']);
        $this->assertSame('KeyAnyWhere (pessoa@example.com)', $dados['codinome']);
        $this->assertStringStartsWith('<?xml', $dados['svgQrCode']);
        $this->assertStringContainsString('<svg', $dados['svgQrCode']);
        $this->assertStringContainsString('width="321"', $dados['svgQrCode']);
    }
}
