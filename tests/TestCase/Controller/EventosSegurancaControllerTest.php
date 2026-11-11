<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Log\Seguranca;
use App\Model\Table\EventosSegurancaTable;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Tela "Eventos de segurança": todos veem os eventos de todos; só o root vê o IP.
 *
 * @uses \App\Controller\EventosSegurancaController
 */
class EventosSegurancaControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use CsrfTestTrait;
    use AuthenticatedTestTrait;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Sessions',
        'app.Auditorias',
        'app.EventosSeguranca',
    ];

    /**
     * Um evento do root (id 1) e um do usuário comum (id 2).
     *
     * @return void
     */
    private function eventosDeDoisUsuarios(): void
    {
        $this->loginAsNonRootUser();
        $eventos = $this->fetchTable(EventosSegurancaTable::class);
        $this->assertTrue($eventos->registrar(Seguranca::XSS_DETECTADO, 'alert', 1, '198.51.100.1'));
        $this->assertTrue($eventos->registrar(Seguranca::LOGIN_CREDENCIAL_INVALIDA, 'notice', 2, '198.51.100.2'));
    }

    /**
     * @return void
     */
    public function testIndexExigeAutenticacao(): void
    {
        $this->get('/eventos-seguranca');

        $this->assertTrue(in_array($this->_response?->getStatusCode(), [302, 401], true));
    }

    /**
     * O usuário comum vê os eventos de todos os usuários, com o nome, mas sem o IP.
     *
     * @return void
     */
    public function testUsuarioComumVeOsEventosDeTodosSemIp(): void
    {
        $this->eventosDeDoisUsuarios();
        $this->loginAsNonRootUser();

        $this->get('/eventos-seguranca');

        $this->assertResponseOk();
        $this->assertResponseContains('Login com senha incorreta');
        $this->assertResponseContains('Conteúdo malicioso bloqueado');
        $this->assertResponseContains('Usuário');
        $this->assertResponseNotContains('IP de origem');
        $this->assertResponseNotContains('198.51.100.1');
        $this->assertResponseNotContains('198.51.100.2');
    }

    /**
     * O root vê os eventos de todos, com o IP de origem.
     *
     * @return void
     */
    public function testRootVeTodosComIp(): void
    {
        $this->eventosDeDoisUsuarios();
        $this->loginAsUser(['root' => true]);

        $this->get('/eventos-seguranca');

        $this->assertResponseOk();
        $this->assertResponseContains('Login com senha incorreta');
        $this->assertResponseContains('Conteúdo malicioso bloqueado');
        $this->assertResponseContains('IP de origem');
        $this->assertResponseContains('198.51.100.1');
        $this->assertResponseContains('198.51.100.2');
    }

    /**
     * A home mostra os últimos eventos de todos, sem IP, com o link para a lista.
     *
     * @return void
     */
    public function testHomeMostraOsUltimosEventosDeTodos(): void
    {
        $this->eventosDeDoisUsuarios();
        $this->loginAsNonRootUser();

        $this->get('/');

        $this->assertResponseOk();
        $this->assertResponseContains('Eventos de segurança');
        $this->assertResponseContains('Login com senha incorreta');
        $this->assertResponseContains('Conteúdo malicioso bloqueado');
        $this->assertResponseNotContains('198.51.100.');
        $this->assertResponseContains('/eventos-seguranca');
    }

    /**
     * Sem eventos, a tela e a home dizem que não há registro.
     *
     * @return void
     */
    public function testSemEventos(): void
    {
        $this->loginAsNonRootUser();

        $this->get('/eventos-seguranca');
        $this->assertResponseContains('Nenhum evento de segurança registrado.');

        $this->get('/');
        $this->assertResponseContains('Nenhum evento de segurança registrado.');
    }
}
