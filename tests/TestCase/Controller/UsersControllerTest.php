<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PragmaRX\Google2FA\Google2FA;

/**
 * App\Controller\UsersController Test Case
 *
 * @uses \App\Controller\UsersController
 */
class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use AuthenticatedTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.Users',
        'app.Categorias',
        'app.Entradas',
        'app.IpsBloqueados',
    ];

    /**
     * Gera um valor aleatório para uso como senha nos testes (não é um segredo real,
     * apenas evita ter no código-fonte uma string estática com "cara" de senha).
     * Sempre atende à regra de complexidade de UsersTable (mínimo 12 caracteres,
     * letra, dígito e símbolo).
     *
     * @return string
     */
    private static function gerarSenhaDeTeste(): string
    {
        return 'Aa1!' . bin2hex(random_bytes(8));
    }

    /**
     * Test index method - requires authentication
     *
     * @return void
     * @uses \App\Controller\UsersController::index()
     */
    public function testIndex(): void
    {
        $this->get('/users');
        // Routes are protected, should return 401 (Unauthorized) or 302 (Redirect)
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Test add method - GET request without auth should redirect to login
     *
     * @return void
     * @uses \App\Controller\UsersController::add()
     */
    public function testAdd(): void
    {
        $this->get('/users/add');
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
        $this->post('/users/add', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'senha' => 'password123',
        ]);
        // Routes are protected, should return 401 (Unauthorized) or 302 (Redirect)
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Test edit method - requires authentication
     *
     * @return void
     * @uses \App\Controller\UsersController::edit()
     */
    public function testEdit(): void
    {
        $this->get('/users/edit/1');
        // Routes are protected, should return 401 (Unauthorized) or 302 (Redirect)
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Test delete method - requires authentication
     *
     * @return void
     * @uses \App\Controller\UsersController::delete()
     */
    public function testDelete(): void
    {
        $this->post('/users/delete', ['id' => '1']);
        // Routes are protected, should return 401 (Unauthorized) or 302 (Redirect)
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * Test that index requires GET method
     *
     * @return void
     */
    public function testIndexRequiresGet(): void
    {
        $this->post('/users', []);
        // Should not allow POST to index
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401, 405]));
    }

    /**
     * Test edit method - POST request without auth
     *
     * @return void
     */
    public function testEditPostRequestRequiresAuthentication(): void
    {
        $this->post('/users/edit/1', [
            'username' => 'updateduser',
        ]);
        // Routes are protected
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * index deve listar os usuários (não-root) para um usuário root autenticado.
     *
     * @return void
     */
    public function testIndexComoRoot(): void
    {
        $this->loginAsUser(['root' => true]);
        $this->get('/users');
        $this->assertResponseOk();
    }

    /**
     * Um POST autenticado como root com dados válidos deve criar o usuário no banco.
     *
     * @return void
     */
    public function testAddComoRootPersisteNovoUsuario(): void
    {
        $this->loginAsUser(['root' => true]);
        $this->post('/users/add', [
            'username' => 'novo_usuario',
            'email' => 'novo_usuario@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $this->assertResponseSuccess();
        $users = $this->getTableLocator()->get('Users');
        $this->assertTrue($users->exists(['username' => 'novo_usuario']));
    }

    /**
     * Um POST autenticado como root em edit deve persistir a alteração no banco.
     *
     * @return void
     */
    public function testEditComoRootPersisteAlteracao(): void
    {
        $this->loginAsUser(['root' => true]);
        $users = $this->getTableLocator()->get('Users');
        $original = $users->get(1);

        $this->post('/users/edit/1', ['username' => 'usuario_renomeado', 'email' => $original->email]);

        $this->assertResponseSuccess();
        $recarregado = $users->get(1);
        $this->assertSame('usuario_renomeado', $recarregado->username);
    }

    /**
     * Um POST autenticado como root em delete deve remover o usuário do banco.
     *
     * @return void
     */
    public function testDeleteComoRootRemoveUsuarioDoBanco(): void
    {
        $this->loginAsUser(['root' => true]);
        $users = $this->getTableLocator()->get('Users');
        $descartavel = $users->save($users->newEntity([
            'username' => 'usuario_descartavel',
            'email' => 'descartavel@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]));

        $this->post('/users/delete', ['id' => $descartavel->id]);

        $this->assertResponseSuccess();
        $this->assertFalse($users->exists(['id' => $descartavel->id]));
    }

    /**
     * RN: um usuário autenticado não-root deve ser bloqueado (e redirecionado) ao
     * tentar acessar as ações root-only (index/add/edit/delete) — ver
     * UsersController::SOMENTE_ROOT_ACESSA / beforeFilter().
     *
     * @return void
     */
    public function testIndexBloqueadoParaUsuarioNaoRoot(): void
    {
        $this->loginAsNonRootUser();
        $this->get('/users');

        $this->assertRedirect(['controller' => 'Pages', 'action' => 'home']);
    }

    /**
     * Login com email e senha corretos, seguido de um código 2FA válido,
     * deve autenticar o usuário e redirecionar para a home.
     *
     * @return void
     */
    public function testLoginComCredenciaisECodigo2faValidosAutentica(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $senha = self::gerarSenhaDeTeste();

        $users = $this->getTableLocator()->get('Users');
        $user = $users->save($users->newEntity([
            'username' => 'usuario_login',
            'email' => 'usuario_login@example.com',
            'password' => $senha,
            'tfa_ativo' => true,
        ]));
        $user->tfa_secret = $secret;
        $users->save($user);

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/users/login', [
            'email' => 'usuario_login@example.com',
            'password' => $senha,
            '2fa' => $google2fa->getCurrentOtp($secret),
        ]);

        $this->assertRedirect(['controller' => 'Pages', 'action' => 'home']);
    }

    /**
     * Login com senha incorreta não deve autenticar o usuário.
     *
     * @return void
     */
    public function testLoginComSenhaIncorretaNaoAutentica(): void
    {
        $users = $this->getTableLocator()->get('Users');
        $users->save($users->newEntity([
            'username' => 'usuario_login2',
            'email' => 'usuario_login2@example.com',
            'password' => self::gerarSenhaDeTeste(),
            'tfa_ativo' => true,
        ]));

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/users/login', [
            'email' => 'usuario_login2@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $this->assertResponseOk();
    }

    /**
     * Com o sistema sem nenhum usuário, configInicial deve exibir o formulário
     * de bootstrap (não redirecionar para o login) — ver
     * UsersController::executarConfigInicial() / configInicial().
     *
     * @return void
     */
    public function testConfigInicialSemUsuariosExibeFormulario(): void
    {
        $this->getTableLocator()->get('Users')->deleteAll([]);

        $this->get('/users/config-inicial');

        $this->assertResponseOk();
    }

    /**
     * POST em configInicial, sem nenhum usuário existente, deve criar o
     * primeiro usuário como root e redirecionar para configInicialTfa.
     *
     * @return void
     */
    public function testConfigInicialPostCriaPrimeiroUsuarioRoot(): void
    {
        $this->getTableLocator()->get('Users')->deleteAll([]);

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/users/config-inicial', [
            'username' => 'primeiro_root',
            'email' => 'primeiro_root@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $this->assertRedirect(['controller' => 'Users', 'action' => 'configInicialTfa']);
        $users = $this->getTableLocator()->get('Users');
        $criado = $users->find()->where(['username' => 'primeiro_root'])->first();
        $this->assertNotNull($criado);
        $this->assertTrue($criado->root);
    }

    /**
     * Com um usuário criado mas 2FA ainda não ativado, configInicialTfa deve
     * exibir o QR Code e gerar um novo tfa_secret para esse usuário.
     *
     * @return void
     */
    public function testConfigInicialTfaExibeQrCodeParaUsuarioSemTfaAtivo(): void
    {
        $users = $this->getTableLocator()->get('Users');
        $users->deleteAll([]);
        $user = $users->save($users->newEntity([
            'username' => 'usuario_bootstrap',
            'email' => 'usuario_bootstrap@example.com',
            'password' => self::gerarSenhaDeTeste(),
            'root' => true,
        ]));
        $this->assertFalse((bool)$user->tfa_ativo);

        $this->get('/users/config-inicial-tfa');

        $this->assertResponseOk();
        $recarregado = $users->get($user->id);
        $this->assertNotEmpty($recarregado->tfa_secret);
    }

    /**
     * POST em configInicialTfa deve ativar o 2FA do usuário e redirecionar para o login.
     *
     * @return void
     */
    public function testConfigInicialTfaPostAtivaTfaERedirecionaParaLogin(): void
    {
        $users = $this->getTableLocator()->get('Users');
        $users->deleteAll([]);
        $user = $users->save($users->newEntity([
            'username' => 'usuario_bootstrap2',
            'email' => 'usuario_bootstrap2@example.com',
            'password' => self::gerarSenhaDeTeste(),
            'root' => true,
        ]));

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/users/config-inicial-tfa', ['id' => $user->id, 'tfa' => true]);

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
        $recarregado = $users->get($user->id);
        $this->assertTrue($recarregado->tfa_ativo);
    }
}
