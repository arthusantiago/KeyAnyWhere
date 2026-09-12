<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

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
}
