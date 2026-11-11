<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\EventoSeguranca;
use App\Model\Entity\User;
use Cake\TestSuite\TestCase;

/**
 * @uses \App\Model\Entity\EventoSeguranca
 */
class EventoSegurancaTest extends TestCase
{
    /**
     * @return void
     */
    public function testDescricaoUsuarioComUsuarioCarregado(): void
    {
        $evento = new EventoSeguranca(['usuario_id' => 1, 'user' => new User(['id' => 1, 'username' => 'fulano'])]);

        $this->assertSame('fulano', $evento->descricaoUsuario());
    }

    /**
     * Login com um e-mail que não pertence a nenhuma conta.
     *
     * @return void
     */
    public function testDescricaoUsuarioSemConta(): void
    {
        $evento = new EventoSeguranca(['usuario_id' => null, 'user' => null]);

        $this->assertSame('—', $evento->descricaoUsuario());
    }

    /**
     * @return void
     */
    public function testDescricaoUsuarioExcluido(): void
    {
        $evento = new EventoSeguranca(['usuario_id' => 7, 'user' => null]);

        $this->assertSame('Usuário excluído (#7)', $evento->descricaoUsuario());
    }
}
