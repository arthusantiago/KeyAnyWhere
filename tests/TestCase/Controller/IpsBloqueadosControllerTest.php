<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\IpsBloqueadosController Test Case
 *
 * @uses \App\Controller\IpsBloqueadosController
 */
class IpsBloqueadosControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use AuthenticatedTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.IpsBloqueados',
        'app.Users',
    ];

    /**
     * Test index method - requires authentication
     *
     * @return void
     */
    public function testIndexRequiresAuthentication(): void
    {
        $this->get('/ips-bloqueados');
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * index deve listar os IPs bloqueados existentes para um usuário autenticado.
     *
     * @return void
     */
    public function testIndexAsAuthenticatedUser(): void
    {
        $this->loginAsUser();
        $this->get('/ips-bloqueados');
        $this->assertResponseOk();
    }

    /**
     * Test add method - GET request without auth should redirect to login
     *
     * @return void
     */
    public function testAddGetRequestRequiresAuthentication(): void
    {
        $this->get('/ips-bloqueados/add');
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Test add method - POST request without auth should redirect to login
     *
     * @return void
     */
    public function testAddPostRequestRequiresAuthentication(): void
    {
        $this->post('/ips-bloqueados/add', ['ip' => '192.168.1.1']);
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Um POST autenticado com dados válidos deve criar o IP bloqueado no banco.
     *
     * @return void
     */
    public function testAddPersisteNovoIpBloqueado(): void
    {
        $this->loginAsUser();
        $this->post('/ips-bloqueados/add', ['ip' => '198.51.100.55']);

        $this->assertResponseSuccess();
        $ipsBloqueados = $this->getTableLocator()->get('IpsBloqueados');
        $this->assertTrue($ipsBloqueados->exists(['ip' => '198.51.100.55']));
    }

    /**
     * Um POST autenticado com um IP em formato inválido não deve criar o registro.
     *
     * @return void
     */
    public function testAddComIpInvalidoNaoCria(): void
    {
        $this->loginAsUser();
        $ipsBloqueados = $this->getTableLocator()->get('IpsBloqueados');
        $totalAntes = $ipsBloqueados->find()->count();

        $this->post('/ips-bloqueados/add', ['ip' => 'nao-e-um-ip']);

        $this->assertResponseOk();
        $this->assertSame($totalAntes, $ipsBloqueados->find()->count());
    }

    /**
     * Um POST autenticado com um IP já cadastrado não deve duplicar o registro.
     *
     * @return void
     */
    public function testAddComIpDuplicadoNaoCria(): void
    {
        $this->loginAsUser();
        $ipsBloqueados = $this->getTableLocator()->get('IpsBloqueados');
        $totalAntes = $ipsBloqueados->find()->count();

        // '203.0.113.10' já existe na fixture.
        $this->post('/ips-bloqueados/add', ['ip' => '203.0.113.10']);

        $this->assertResponseOk();
        $this->assertSame($totalAntes, $ipsBloqueados->find()->count());
    }

    /**
     * Test delete method - requires POST or DELETE
     *
     * @return void
     */
    public function testDeleteRequiresPostOrDelete(): void
    {
        $this->get('/ips-bloqueados/delete/1');
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401, 405]));
    }

    /**
     * Test delete method - POST request without auth should redirect to login
     *
     * @return void
     */
    public function testDeletePostRequestRequiresAuthentication(): void
    {
        $this->post('/ips-bloqueados/delete', ['id' => '1']);
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Um POST autenticado em delete deve remover o IP bloqueado do banco.
     *
     * @return void
     */
    public function testDeleteRemoveIpBloqueadoDoBanco(): void
    {
        $this->loginAsUser();
        $this->post('/ips-bloqueados/delete', ['id' => 1]);

        $this->assertResponseSuccess();
        $ipsBloqueados = $this->getTableLocator()->get('IpsBloqueados');
        $this->assertFalse($ipsBloqueados->exists(['id' => 1]));
    }

    /**
     * Excluir um IP bloqueado inexistente deve retornar 404.
     *
     * @return void
     */
    public function testDeleteIpBloqueadoInexistenteRetorna404(): void
    {
        $this->loginAsUser();
        $this->post('/ips-bloqueados/delete', ['id' => 9999]);

        $this->assertResponseCode(404);
    }
}
