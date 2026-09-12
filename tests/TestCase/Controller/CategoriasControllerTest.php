<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\CategoriasController Test Case
 *
 * @uses \App\Controller\CategoriasController
 */
class CategoriasControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use AuthenticatedTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.Categorias',
        'app.Entradas',
        'app.Users',
    ];

    /**
     * Test index method - requires authentication
     *
     * @return void
     */
    public function testIndexRequiresAuthentication(): void
    {
        $this->get('/categorias');
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * index deve listar as categorias existentes para um usuário autenticado.
     *
     * @return void
     */
    public function testIndexAsAuthenticatedUser(): void
    {
        $this->loginAsUser();
        $this->get('/categorias');
        $this->assertResponseOk();
    }

    /**
     * Test add method - GET request without auth should redirect to login
     *
     * @return void
     * @uses \App\Controller\CategoriasController::add()
     */
    public function testAddGetRequestRequiresAuthentication(): void
    {
        $this->get('/categorias/add');
        // Routes are protected, should return 401 (Unauthorized) during tests or 302 in production
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Test add method - POST request without auth should redirect to login
     *
     * @return void
     */
    public function testAddPostRequestRequiresAuthentication(): void
    {
        $this->post('/categorias/add', ['nome' => 'Categoria Sem Autenticacao']);
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Um POST autenticado com dados válidos deve criar a categoria no banco e redirecionar.
     *
     * @return void
     */
    public function testAddPersisteNovaCategoria(): void
    {
        $this->loginAsUser();
        $this->post('/categorias/add', ['nome' => 'Nova Categoria']);

        $this->assertResponseSuccess();
        $categorias = $this->getTableLocator()->get('Categorias');
        $criada = $categorias->find()->orderByDesc('id')->first();
        $this->assertSame('Nova Categoria', $criada->nomeDescrip());
    }

    /**
     * Um POST autenticado com nome vazio não deve criar a categoria (fica na tela com erro).
     *
     * @return void
     */
    public function testAddComNomeVazioNaoCriaCategoria(): void
    {
        $this->loginAsUser();
        $categorias = $this->getTableLocator()->get('Categorias');
        $totalAntes = $categorias->find()->count();

        $this->post('/categorias/add', ['nome' => '']);

        $this->assertResponseOk();
        $this->assertSame($totalAntes, $categorias->find()->count());
    }

    /**
     * Test edit method - GET request without auth should redirect to login
     *
     * @return void
     */
    public function testEditGetRequestRequiresAuthentication(): void
    {
        $this->get('/categorias/edit/1');
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Test edit method - POST request without auth should redirect to login
     *
     * @return void
     */
    public function testEditPostRequestRequiresAuthentication(): void
    {
        $this->post('/categorias/edit/1', ['nome' => 'Alterada Sem Autenticacao']);
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * GET em edit, autenticado, deve renderizar o formulário com a categoria da fixture.
     *
     * @return void
     */
    public function testEditGetAsAuthenticatedUser(): void
    {
        $this->loginAsUser();
        $this->get('/categorias/edit/1');
        $this->assertResponseOk();
    }

    /**
     * Um POST autenticado em edit deve persistir a alteração do nome no banco.
     *
     * @return void
     */
    public function testEditPersisteAlteracao(): void
    {
        $this->loginAsUser();
        $this->post('/categorias/edit/1', ['nome' => 'Categoria Renomeada']);

        $this->assertResponseSuccess();
        $categorias = $this->getTableLocator()->get('Categorias');
        $recarregada = $categorias->get(1);
        $this->assertSame('Categoria Renomeada', $recarregada->nomeDescrip());
    }

    /**
     * Um POST autenticado em edit com nome vazio não deve alterar a categoria (fica com o erro).
     *
     * @return void
     */
    public function testEditComNomeVazioNaoAlteraCategoria(): void
    {
        $this->loginAsUser();
        $categorias = $this->getTableLocator()->get('Categorias');
        $nomeOriginal = $categorias->get(1)->nomeDescrip();

        $this->post('/categorias/edit/1', ['nome' => '']);

        $this->assertResponseSuccess();
        $recarregada = $categorias->get(1);
        $this->assertSame($nomeOriginal, $recarregada->nomeDescrip());
    }

    /**
     * Editar uma categoria inexistente deve retornar 404.
     *
     * @return void
     */
    public function testEditCategoriaInexistenteRetorna404(): void
    {
        $this->loginAsUser();
        $this->get('/categorias/edit/9999');
        $this->assertResponseCode(404);
    }

    /**
     * Test delete method - requires POST or DELETE
     *
     * @return void
     */
    public function testDeleteRequiresPostOrDelete(): void
    {
        $this->get('/categorias/delete/1');
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401, 405]));
    }

    /**
     * Test delete method - POST request without auth should redirect to login
     *
     * @return void
     */
    public function testDeletePostRequestRequiresAuthentication(): void
    {
        $this->post('/categorias/delete', ['id' => '1']);
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Um POST autenticado em delete deve remover a categoria do banco.
     *
     * @return void
     */
    public function testDeleteRemoveCategoriaDoBanco(): void
    {
        $this->loginAsUser();
        $categorias = $this->getTableLocator()->get('Categorias');
        $entradas = $this->getTableLocator()->get('Entradas');
        // Remove a entrada dependente para não violar a FK ao excluir a categoria.
        $entradas->deleteAll(['categoria_id' => 1]);

        $this->post('/categorias/delete', ['id' => 1]);

        $this->assertResponseSuccess();
        $this->assertFalse($categorias->exists(['id' => 1]));
    }

    /**
     * Test listagemEntradas method - requires authentication
     *
     * @return void
     */
    public function testListagemEntradasRequiresAuthentication(): void
    {
        $this->get('/categorias/listagem-entradas/1');
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * listagemEntradas deve listar, para um usuário autenticado, as entradas
     * pertencentes à categoria informada.
     *
     * @return void
     */
    public function testListagemEntradasListaEntradasDaCategoria(): void
    {
        $this->loginAsUser();
        $this->get('/categorias/listagem-entradas/1');

        $this->assertResponseOk();
        $this->assertResponseContains('Entrada de Teste');
    }

    /**
     * listagemEntradas de uma categoria inexistente deve retornar 404.
     *
     * @return void
     */
    public function testListagemEntradasCategoriaInexistenteRetorna404(): void
    {
        $this->loginAsUser();
        $this->get('/categorias/listagem-entradas/9999');
        $this->assertResponseCode(404);
    }
}
