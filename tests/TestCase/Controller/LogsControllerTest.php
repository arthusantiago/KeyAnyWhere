<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * App\Controller\LogsController Test Case
 *
 * @uses \App\Controller\LogsController
 */
class LogsControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use AuthenticatedTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.Logs',
        'app.Users',
        'app.Auditorias',
    ];

    /**
     * Test index method - requires authentication
     *
     * @return void
     */
    public function testIndexRequiresAuthentication(): void
    {
        $this->get('/logs');
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * index deve listar os logs existentes para um usuário autenticado.
     *
     * @return void
     */
    public function testIndexAsAuthenticatedUser(): void
    {
        $this->loginAsUser();
        $this->get('/logs');
        $this->assertResponseOk();
    }

    /**
     * Test view method - requires authentication
     *
     * @return void
     */
    public function testViewRequiresAuthentication(): void
    {
        $this->get('/logs/view/1');
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * view deve exibir o detalhe do log da fixture para um usuário autenticado.
     *
     * @return void
     */
    public function testViewAsAuthenticatedUser(): void
    {
        $this->loginAsUser();
        $this->get('/logs/view/1');
        $this->assertResponseOk();
    }

    /**
     * Visualizar um log inexistente deve retornar 404.
     *
     * @return void
     */
    public function testViewLogInexistenteRetorna404(): void
    {
        $this->loginAsUser();
        $this->get('/logs/view/9999');
        $this->assertResponseCode(404);
    }

    /**
     * Test analisado method - requires authentication
     *
     * @return void
     */
    public function testAnalisadoRequiresAuthentication(): void
    {
        $this->get('/logs/analisado/1');
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Test analisado method - POST should not be allowed (allowMethod(['get']))
     *
     * @return void
     */
    public function testAnalisadoPostNotAllowed(): void
    {
        $this->loginAsUser();
        $this->post('/logs/analisado/1', []);
        $this->assertTrue(in_array($this->_response->getStatusCode(), [405]));
    }

    /**
     * analisado deve inverter o campo 'analisado' do log e persistir no banco.
     *
     * @return void
     */
    public function testAnalisadoInverteEPersisteFlag(): void
    {
        $this->loginAsUser();
        $logs = $this->getTableLocator()->get('Logs');
        $antes = $logs->get(1)->analisado;

        $this->get('/logs/analisado/1');

        $this->assertResponseSuccess();
        $depois = $logs->get(1)->analisado;
        $this->assertNotSame($antes, $depois);
    }

    /**
     * Chamar analisado duas vezes deve reverter o campo ao valor original (toggle).
     *
     * @return void
     */
    public function testAnalisadoChamadoDuasVezesRestauraValorOriginal(): void
    {
        $this->loginAsUser();
        $logs = $this->getTableLocator()->get('Logs');
        $original = $logs->get(1)->analisado;

        $this->get('/logs/analisado/1');
        $this->get('/logs/analisado/1');

        $this->assertResponseSuccess();
        $final = $logs->get(1)->analisado;
        $this->assertSame($original, $final);
    }

    /**
     * Marcar como analisado um log inexistente deve retornar 404.
     *
     * @return void
     */
    public function testAnalisadoLogInexistenteRetorna404(): void
    {
        $this->loginAsUser();
        $this->get('/logs/analisado/9999');
        $this->assertResponseCode(404);
    }

    /**
     * Rotas de Logs. Todas são somente root: o menu só mostra Logs na seção de root, e um
     * usuário comum não pode ler os logs de segurança nem marcá-los como analisados.
     *
     * @return array<string, array{string}>
     */
    public static function rotasLogsProvider(): array
    {
        return [
            'index' => ['/logs'],
            'view' => ['/logs/view/1'],
            'analisado' => ['/logs/analisado/1'],
        ];
    }

    /**
     * RN: um usuário autenticado não root é redirecionado para a home, sem executar a action.
     *
     * @param string $url
     * @return void
     */
    #[DataProvider('rotasLogsProvider')]
    public function testRotasDeLogsBloqueadasParaUsuarioNaoRoot(string $url): void
    {
        $logs = $this->getTableLocator()->get('Logs');
        $analisadoAntes = $logs->get(1)->get('analisado');

        $this->loginAsNonRootUser();
        $this->get($url);

        $this->assertRedirect(['controller' => 'Pages', 'action' => 'home']);
        $this->assertSame($analisadoAntes, $logs->get(1)->get('analisado'), 'A action não deveria ter rodado.');
    }
}
