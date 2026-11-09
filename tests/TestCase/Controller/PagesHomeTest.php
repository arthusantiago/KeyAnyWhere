<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Home ("Minha Segurança"): visível para todo usuário logado.
 *
 * @uses \App\Controller\PagesController::home()
 */
class PagesHomeTest extends TestCase
{
    use IntegrationTestTrait;
    use AuthenticatedTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Logs',
        'app.Sessions',
        'app.Auditorias',
    ];

    /**
     * O root vê as atividades suspeitas e o link para a lista completa de logs.
     *
     * @return void
     */
    public function testRootVeLinkParaListaCompletaDeLogs(): void
    {
        $this->loginAsUser();
        $this->get('/');

        $this->assertResponseOk();
        $this->assertResponseContains('Atividades suspeitas');
        $this->assertResponseContains('Lista completa');
    }

    /**
     * O usuário comum vê o resumo das atividades suspeitas, mas não o link para os logs:
     * a tela de Logs é somente root.
     *
     * @return void
     */
    public function testUsuarioComumNaoVeLinkParaOsLogs(): void
    {
        $this->loginAsNonRootUser();
        $this->get('/');

        $this->assertResponseOk();
        $this->assertResponseContains('Atividades suspeitas');
        $this->assertResponseNotContains('Lista completa');
    }

    /**
     * A funcionalidade "IPs Bloqueados" foi removida (2026-11-09).
     *
     * @return void
     */
    public function testHomeNaoMostraMaisIpsBloqueados(): void
    {
        $this->loginAsUser();
        $this->get('/');

        $this->assertResponseOk();
        $this->assertResponseNotContains('IPs bloqueados');
        $this->assertResponseNotContains('ips-bloqueados');
    }
}
