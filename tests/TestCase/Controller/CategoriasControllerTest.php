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
     * Test add method
     *
     * @return void
     * @uses \App\Controller\CategoriasController::add()
     */
    public function testAdd(): void
    {
        $this->testAddGetRequestRequiresAuthentication();
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
}
