<?php
declare(strict_types=1);

namespace App\Test\TestCase\View\Tools;

use App\View\AppView;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;

/**
 * @uses \App\View\Tools\ManipuladorMensagemValidatorTrait
 */
class ManipuladorMensagemValidatorTraitTest extends TestCase
{
    /**
     * As mensagens saem em lista simples, das entidades e das mensagens avulsas.
     *
     * @return void
     */
    public function testExtraiMensagensDeEntidadesEDeMensagens(): void
    {
        $entidade = new Entity();
        $entidade->setError('nome', ['maxLength' => 'Nome longo demais']);

        $mensagens = (new AppView())->extraiMensagens([
            'entidades' => [$entidade],
            'mensagens' => ['email' => ['email' => 'E-mail inválido'], 'Outro erro'],
        ]);

        $this->assertSame(['Nome longo demais', 'E-mail inválido', 'Outro erro'], $mensagens);
    }

    /**
     * Sem `mensagens` nem `entidades`, não há o que extrair.
     *
     * @return void
     */
    public function testExtraiMensagensSemDados(): void
    {
        $this->assertSame([], (new AppView())->extraiMensagens([]));
    }

    /**
     * @return void
     */
    public function testAdicionaNumeradorEConcatenaMensagens(): void
    {
        $view = new AppView();
        $numeradas = $view->adicionaNumerador(['Erro A', 'Erro B']);

        $this->assertSame(['1- Erro A', '2- Erro B'], $numeradas);
        $this->assertSame('1- Erro A | 2- Erro B', $view->concatenaMensagens($numeradas));
        $this->assertSame('1- Erro A; 2- Erro B', $view->concatenaMensagens($numeradas, '; '));
    }
}
