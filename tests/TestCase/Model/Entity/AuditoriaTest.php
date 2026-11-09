<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Auditoria;
use App\Model\Entity\User;
use Cake\TestSuite\TestCase;

/**
 * @uses \App\Model\Entity\Auditoria
 */
class AuditoriaTest extends TestCase
{
    /**
     * @return void
     */
    public function testDescricaoUsuarioComUsuarioCarregado(): void
    {
        $auditoria = new Auditoria(['usuario_id' => 1, 'user' => new User(['id' => 1, 'username' => 'fulano'])]);

        $this->assertSame('fulano', $auditoria->descricaoUsuario());
    }

    /**
     * @return void
     */
    public function testDescricaoUsuarioSemLogin(): void
    {
        $auditoria = new Auditoria(['usuario_id' => null, 'user' => null]);

        $this->assertSame('Sem usuário autenticado', $auditoria->descricaoUsuario());
    }

    /**
     * @return void
     */
    public function testDescricaoUsuarioExcluido(): void
    {
        $auditoria = new Auditoria(['usuario_id' => 7, 'user' => null]);

        $this->assertSame('Usuário excluído (#7)', $auditoria->descricaoUsuario());
    }
}
