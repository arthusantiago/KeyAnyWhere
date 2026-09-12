<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\EntradasController Test Case
 *
 * @uses \App\Controller\EntradasController
 */
class EntradasControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use AuthenticatedTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.Entradas',
        'app.Categorias',
        'app.Users',
        'app.InsecurePasswords',
    ];

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
     * Test add method - GET request without auth should redirect to login
     *
     * @return void
     * @uses \App\Controller\EntradasController::add()
     */
    public function testAdd(): void
    {
        $this->get('/entradas/add');
        // Routes are protected, should return 401 (Unauthorized) or 302 (Redirect)
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Test add method - POST request without auth should redirect to login
     *
     * @return void
     */
    public function testAddPostRequestRequiresAuthentication(): void
    {
        $this->post('/entradas/add', [
            'titulo' => 'Test entrada',
            'categoria_id' => 1,
        ]);
        // Routes are protected, should return 401 (Unauthorized) or 302 (Redirect)
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Test edit method - requires authentication
     *
     * @return void
     * @uses \App\Controller\EntradasController::edit()
     */
    public function testEdit(): void
    {
        $this->get('/entradas/edit/1');
        // Routes are protected, should return 401 (Unauthorized) or 302 (Redirect)
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Test edit method - POST request without auth
     *
     * @return void
     */
    public function testEditPostRequestRequiresAuthentication(): void
    {
        $this->post('/entradas/edit/1', [
            'titulo' => 'Updated entrada',
        ]);
        // Routes are protected
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Test delete method - requires authentication
     *
     * @return void
     * @uses \App\Controller\EntradasController::delete()
     */
    public function testDelete(): void
    {
        $this->post('/entradas/delete', ['id' => '1']);
        // Routes are protected, should return 401 (Unauthorized) or 302 (Redirect)
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Test delete method - DELETE request without auth
     *
     * @return void
     */
    public function testDeleteRequiresPostOrDelete(): void
    {
        $this->delete('/entradas/delete/1', []);
        // Routes are protected
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Um POST autenticado com dados válidos deve criar a entrada no banco.
     *
     * @return void
     */
    public function testAddPersisteNovaEntrada(): void
    {
        $this->loginAsUser();
        $this->post('/entradas/add', [
            'titulo' => 'Nova Entrada',
            'username' => 'novo.usuario',
            'password' => self::gerarSenhaDeTeste(),
            'categoria_id' => 1,
            'anotacoes' => '',
        ]);

        $this->assertResponseSuccess();
        $entradas = $this->getTableLocator()->get('Entradas');
        $criada = $entradas->find()->orderByDesc('id')->first();
        $this->assertSame('Nova Entrada', $criada->tituloDescrip());
    }

    /**
     * Um POST autenticado em edit deve persistir a alteração do título no banco.
     *
     * @return void
     */
    public function testEditPersisteAlteracao(): void
    {
        $this->loginAsUser();
        $entradas = $this->getTableLocator()->get('Entradas');
        $original = $entradas->get(1);

        $this->post('/entradas/edit/1', [
            'titulo' => 'Título Atualizado',
            'username' => $original->usernameDescrip(),
            'password' => $original->passwordDescrip(),
            'categoria_id' => $original->categoria_id,
            'anotacoes' => $original->anotacoes,
        ]);

        $this->assertResponseSuccess();
        $recarregada = $entradas->get(1);
        $this->assertSame('Título Atualizado', $recarregada->tituloDescrip());
    }

    /**
     * Um POST autenticado em delete deve remover a entrada do banco.
     *
     * @return void
     */
    public function testDeleteRemoveEntradaDoBanco(): void
    {
        $this->loginAsUser();
        $this->post('/entradas/delete', ['id' => 1]);

        $this->assertResponseSuccess();
        $entradas = $this->getTableLocator()->get('Entradas');
        $this->assertFalse($entradas->exists(['id' => 1]));
    }

    /**
     * clipboard deve retornar a senha descriptografada em JSON quando type=password.
     *
     * @return void
     */
    public function testClipboardRetornaPasswordDescriptografada(): void
    {
        $this->loginAsUser();
        $this->post('/entradas/clipboard', ['id' => 1, 'type' => 'password']);

        $this->assertResponseOk();
        $body = json_decode((string)$this->_response->getBody(), true);
        $this->assertSame('SenhaSuperSecreta123!', $body['data']);
    }

    /**
     * clipboard deve retornar o username descriptografado em JSON quando type=user.
     *
     * @return void
     */
    public function testClipboardRetornaUsernameDescriptografado(): void
    {
        $this->loginAsUser();
        $this->post('/entradas/clipboard', ['id' => 1, 'type' => 'user']);

        $this->assertResponseOk();
        $body = json_decode((string)$this->_response->getBody(), true);
        $this->assertSame('usuario.teste', $body['data']);
    }

    /**
     * busca deve localizar, por substring do título descriptografado, a entrada da fixture.
     * Retorna JSON puro (não HTML) — o cliente monta a lista via DOM, nunca innerHTML,
     * para não expor um sink de DOM XSS client-side.
     *
     * @return void
     */
    public function testBuscaLocalizaEntradaPorTitulo(): void
    {
        $this->loginAsUser();
        $this->post('/entradas/busca', ['stringBusca' => 'entrada de teste']);

        $this->assertResponseOk();
        $resultado = json_decode((string)$this->_response->getBody(), true);
        $this->assertCount(1, $resultado);
        $this->assertSame('Entrada de Teste', $resultado[0]['titulo']);
        $this->assertArrayHasKey('url', $resultado[0]);
    }

    /**
     * busca não deve retornar nenhum resultado para um termo que não corresponde a nenhum título.
     *
     * @return void
     */
    public function testBuscaNaoLocalizaTermoInexistente(): void
    {
        $this->loginAsUser();
        $this->post('/entradas/busca', ['stringBusca' => 'termo-que-nao-existe-em-nada']);

        $this->assertResponseOk();
        $resultado = json_decode((string)$this->_response->getBody(), true);
        $this->assertSame([], $resultado);
    }

    /**
     * senhaInsegura deve indicar 'localizado: true' para uma senha presente no catálogo.
     *
     * @return void
     */
    public function testSenhaInseguraIndicaSenhaPresenteNoCatalogo(): void
    {
        $this->loginAsUser();
        $this->post('/entradas/senha-insegura', ['password' => '123456']);

        $this->assertResponseOk();
        $body = json_decode((string)$this->_response->getBody(), true);
        $this->assertTrue($body['localizado']);
    }

    /**
     * senhaInsegura deve indicar 'localizado: false' para uma senha ausente do catálogo.
     *
     * @return void
     */
    public function testSenhaInseguraIndicaSenhaAusenteDoCatalogo(): void
    {
        $this->loginAsUser();
        $this->post('/entradas/senha-insegura', ['password' => self::gerarSenhaDeTeste()]);

        $this->assertResponseOk();
        $body = json_decode((string)$this->_response->getBody(), true);
        $this->assertFalse($body['localizado']);
    }
}
