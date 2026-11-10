<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Application;
use App\Controller\UsersController;
use App\Log\Seguranca;
use App\Middleware\CsrfProtectionKawMiddleware;
use App\Model\Entity\User;
use App\Model\Table\AuditoriasTable;
use App\Model\Table\UsersTable;
use App\Test\TestCase\Log\CapturaLogSegurancaTrait;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\Event\EventManager;
use Cake\Http\Response;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\Utility\Security;
use PHPUnit\Framework\Attributes\DataProvider;
use PragmaRX\Google2FA\Google2FA;

/**
 * App\Controller\UsersController Test Case
 *
 * @uses \App\Controller\UsersController
 */
class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use CsrfTestTrait;
    use AuthenticatedTestTrait;
    use CapturaLogSegurancaTrait;
    use ConfiguracaoKawIsoladaTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.Users',
        'app.Categorias',
        'app.Entradas',
        'app.Auditorias',
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
        // Paginação (element paginacao) e menu de opções (btnOpcoes) traduzidos
        $this->assertResponseContains('Exibindo');
        $this->assertResponseContains('Usuários');
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
     * Um POST autenticado como root com e-mail inválido não deve criar o usuário.
     *
     * @return void
     */
    public function testAddComoRootComEmailInvalidoNaoCriaUsuario(): void
    {
        $this->loginAsUser(['root' => true]);
        $users = $this->getTableLocator()->get('Users');
        $totalAntes = $users->find()->count();

        $this->post('/users/add', [
            'username' => 'usuario_invalido',
            'email' => 'nao-e-um-email',
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $this->assertResponseOk();
        $this->assertSame($totalAntes, $users->find()->count());
        // Flash de erro sem texto: mensagem padrão e a lista dos erros de validação.
        $this->assertResponseContains('Ocorreu um erro');
        $this->assertResponseContains('E-mail');
    }

    /**
     * RN: index/add/edit/delete são exclusivos do usuário root — um usuário
     * não-root deve ser bloqueado (e redirecionado) em add.
     *
     * @return void
     */
    public function testAddBloqueadoParaUsuarioNaoRoot(): void
    {
        $this->loginAsNonRootUser();
        $this->get('/users/add');

        $this->assertRedirect(['controller' => 'Pages', 'action' => 'home']);
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
        // Flash sem texto: o element usa a mensagem padrão.
        $this->assertResponseContains('Salvo com sucesso');
    }

    /**
     * Um POST autenticado como root em edit com username vazio não deve alterar o usuário.
     *
     * @return void
     */
    public function testEditComoRootComUsernameVazioNaoAlteraUsuario(): void
    {
        $this->loginAsUser(['root' => true]);
        $users = $this->getTableLocator()->get('Users');
        $usernameOriginal = $users->get(1)->username;

        $this->post('/users/edit/1', ['username' => '', 'email' => $users->get(1)->email]);

        $this->assertResponseSuccess();
        $recarregado = $users->get(1);
        $this->assertSame($usernameOriginal, $recarregado->username);
    }

    /**
     * Editar um usuário inexistente deve retornar 404.
     *
     * @return void
     */
    public function testEditComoRootUsuarioInexistenteRetorna404(): void
    {
        $this->loginAsUser(['root' => true]);
        $this->get('/users/edit/9999');

        $this->assertResponseCode(404);
    }

    /**
     * RN: um usuário não-root deve ser bloqueado (e redirecionado) em edit.
     *
     * @return void
     */
    public function testEditBloqueadoParaUsuarioNaoRoot(): void
    {
        $this->loginAsNonRootUser();
        $this->get('/users/edit/1');

        $this->assertRedirect(['controller' => 'Pages', 'action' => 'home']);
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
     * RN: um usuário não-root deve ser bloqueado (e redirecionado) em delete.
     *
     * @return void
     */
    public function testDeleteBloqueadoParaUsuarioNaoRoot(): void
    {
        $this->loginAsNonRootUser();
        $this->post('/users/delete', ['id' => 1]);

        $this->assertRedirect(['controller' => 'Pages', 'action' => 'home']);
        $users = $this->getTableLocator()->get('Users');
        $this->assertTrue($users->exists(['id' => 1]));
    }

    /**
     * Excluir um usuário que já fez alguma ação (aparece em auditorias.usuario_id) funciona,
     * e a trilha mantém o autor. Com a FK `fk_auditorias_usuario_id` (ON DELETE RESTRICT), o
     * DELETE falhava no banco e a exclusão terminava em erro 500.
     *
     * @return void
     */
    public function testDeleteDeUsuarioComRegistrosDeAuditoriaMantemATrilha(): void
    {
        $users = $this->fetchTable(UsersTable::class);
        $usuario = $users->saveOrFail($users->newEntity([
            'username' => 'usuario_com_historico',
            'email' => 'historico@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]));
        $auditorias = $this->fetchTable(AuditoriasTable::class);
        $registro = $auditorias->saveOrFail($auditorias->newEntity([
            'contexto' => 'entrada',
            'id_entidade' => 1,
            'acao' => 'criacao',
            'usuario_id' => $usuario->id,
            'alteracoes' => [],
        ]));

        $this->loginAsUser(['root' => true]);
        $this->post('/users/delete', ['id' => $usuario->id]);

        $this->assertRedirect(['action' => 'index']);
        $this->assertFalse($users->exists(['id' => $usuario->id]));
        $this->assertSame($usuario->id, $auditorias->get($registro->id)->get('usuario_id'));
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
     * Cria um usuário (não-root) com 2FA ativo para os testes de login.
     *
     * A app.Users fixture já tem o usuário de id=1 com tfa_ativo, então
     * UsersController::executarConfigInicial() não desvia o login para a configuração inicial.
     *
     * @return array{email: string, senha: string, secret: string}
     */
    private function criarUsuarioParaLogin(): array
    {
        $credenciais = [
            'email' => 'usuario_login@example.com',
            'senha' => self::gerarSenhaDeTeste(),
            'secret' => (new Google2FA())->generateSecretKey(),
        ];

        $users = $this->getTableLocator()->get('Users');
        $user = $users->newEntity([
            'username' => 'usuario_login',
            'email' => $credenciais['email'],
            'password' => $credenciais['senha'],
        ]);
        if (!$user instanceof User) {
            $this->fail('UsersTable::newEntity() deveria devolver App\\Model\\Entity\\User.');
        }
        // Campos protegidos por User::_accessible — atribuição direta, como faz o controller.
        $user->tfa_ativo = true;
        $user->tfa_secret = $credenciais['secret'];
        $users->saveOrFail($user);

        $this->enableSecurityToken();

        return $credenciais;
    }

    /**
     * Código TOTP válido agora para o segredo informado.
     *
     * @param string $secret
     * @return string
     */
    private static function codigo2faAtual(string $secret): string
    {
        return (new Google2FA())->getCurrentOtp($secret);
    }

    /**
     * Faz o POST de login com o ambiente detectado como PRODUCAO, onde o 2FA é
     * realmente verificado (em DESENVOLVIMENTO, User::valida2fa() aceita qualquer código).
     *
     * Com debug=false o HttpsEnforcerMiddleware passa a exigir HTTPS, por isso a
     * requisição é simulada como HTTPS.
     *
     * @param array<string, mixed> $dados
     * @return void
     */
    private function postLoginEmModoProducao(array $dados): void
    {
        $debugOriginal = Configure::read('debug');
        Configure::write('debug', false);
        $this->configRequest(['environment' => ['HTTPS' => 'on']]);
        // O nome do cookie CSRF depende do debug (ver Application::nomeCookieCsrf()).
        $this->enableCsrfToken(Application::nomeCookieCsrf());

        try {
            $this->post('https://localhost/users/login', $dados);
        } finally {
            Configure::write('debug', $debugOriginal);
        }
    }

    /**
     * Identidade autenticada gravada na sessão pelo último request.
     *
     * Lê $_SESSION diretamente (como o assertSession() do Cake): o Server fecha a sessão
     * ao fim do request e, em CLI, um novo Session::read() a reiniciaria vazia.
     *
     * @return \App\Model\Entity\User|null
     */
    private function identidadeNaSessao(): ?User
    {
        $identidade = $_SESSION['Auth'] ?? null;

        return $identidade instanceof User ? $identidade : null;
    }

    /**
     * Faz o próximo request reutilizar a sessão resultante do último request, como um
     * navegador faria com o cookie de sessão.
     *
     * @return void
     */
    private function reutilizarSessaoDoUltimoRequest(): void
    {
        // session() mescla com o que já estava configurado; zeramos antes para que
        // dados removidos no último request (ex.: 'Auth' no logout) não voltem.
        $this->_session = [];
        $this->session($_SESSION);
    }

    /**
     * Login com email, senha e código 2FA válidos deve gravar a identidade do
     * usuário na sessão e redirecionar para a home.
     *
     * @return void
     */
    public function testLoginComCredenciaisECodigo2faValidosAutentica(): void
    {
        $credenciais = $this->criarUsuarioParaLogin();

        $this->post('/users/login', [
            'email' => $credenciais['email'],
            'password' => $credenciais['senha'],
            '2fa' => self::codigo2faAtual($credenciais['secret']),
        ]);

        $this->assertRedirect(['controller' => 'Pages', 'action' => 'home']);
        $this->assertSame($credenciais['email'], $this->identidadeNaSessao()?->email);
    }

    /**
     * Login com senha incorreta não deve autenticar: fica na tela de login, com a
     * mensagem de erro e sem identidade na sessão.
     *
     * @return void
     */
    public function testLoginComSenhaIncorretaNaoAutentica(): void
    {
        $credenciais = $this->criarUsuarioParaLogin();

        $this->post('/users/login', [
            'email' => $credenciais['email'],
            'password' => self::gerarSenhaDeTeste(),
            '2fa' => self::codigo2faAtual($credenciais['secret']),
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('Credenciais de acesso incorretas');
        $this->assertNull($this->identidadeNaSessao());
    }

    /**
     * Login com email não cadastrado não deve autenticar.
     *
     * @return void
     */
    public function testLoginComEmailInexistenteNaoAutentica(): void
    {
        $credenciais = $this->criarUsuarioParaLogin();

        $this->post('/users/login', [
            'email' => 'nao.cadastrado@example.com',
            'password' => $credenciais['senha'],
            '2fa' => self::codigo2faAtual($credenciais['secret']),
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('Credenciais de acesso incorretas');
        $this->assertNull($this->identidadeNaSessao());
    }

    /**
     * Em produção, senha correta com código 2FA válido deve autenticar.
     *
     * @return void
     */
    public function testLoginEmProducaoComCodigo2faValidoAutentica(): void
    {
        $credenciais = $this->criarUsuarioParaLogin();

        $this->postLoginEmModoProducao([
            'email' => $credenciais['email'],
            'password' => $credenciais['senha'],
            '2fa' => self::codigo2faAtual($credenciais['secret']),
        ]);

        $this->assertRedirect(['controller' => 'Pages', 'action' => 'home']);
        $this->assertSame($credenciais['email'], $this->identidadeNaSessao()?->email);
    }

    /**
     * Em produção, senha correta com código 2FA errado não deve autenticar — a identidade
     * já validada pelo FormAuthenticator tem que ser descartada da sessão.
     *
     * @return void
     */
    public function testLoginEmProducaoComCodigo2faInvalidoNaoAutentica(): void
    {
        $credenciais = $this->criarUsuarioParaLogin();
        // Um código diferente do atual (e com o mesmo formato de 6 dígitos).
        $codigoErrado = str_pad(
            (string)(((int)self::codigo2faAtual($credenciais['secret']) + 500000) % 1000000),
            6,
            '0',
            STR_PAD_LEFT,
        );

        $this->postLoginEmModoProducao([
            'email' => $credenciais['email'],
            'password' => $credenciais['senha'],
            '2fa' => $codigoErrado,
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('Credenciais de acesso incorretas');
        $this->assertNull($this->identidadeNaSessao());
    }

    /**
     * Dados de POST com o campo 2fa ausente ou em formato inválido.
     *
     * @return array<string, array{array<string, mixed>}>
     */
    public static function campo2faAusenteOuInvalidoProvider(): array
    {
        return [
            'campo ausente' => [[]],
            'campo vazio' => [['2fa' => '']],
            'campo como array' => [['2fa' => ['123456']]],
        ];
    }

    /**
     * Em produção, senha correta sem um código 2FA utilizável não deve autenticar nem
     * quebrar com erro 500 (antes, um 2fa ausente gerava TypeError em validarTFA()).
     *
     * @param array<string, mixed> $campo2fa
     * @return void
     */
    #[DataProvider('campo2faAusenteOuInvalidoProvider')]
    public function testLoginEmProducaoSemCodigo2faValidoNaoAutentica(array $campo2fa): void
    {
        $credenciais = $this->criarUsuarioParaLogin();

        $this->postLoginEmModoProducao([
            'email' => $credenciais['email'],
            'password' => $credenciais['senha'],
        ] + $campo2fa);

        $this->assertResponseOk();
        $this->assertResponseContains('Credenciais de acesso incorretas');
        $this->assertNull($this->identidadeNaSessao());
    }

    /**
     * logout não deve funcionar sem autenticação.
     *
     * @return void
     */
    public function testLogoutRequiresAuthentication(): void
    {
        $this->get('/users/logout');
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * logout, para um usuário autenticado, deve remover a identidade da sessão e
     * redirecionar para o login.
     *
     * @return void
     */
    public function testLogoutComoAutenticadoRedirecionaParaLogin(): void
    {
        $this->loginAsUser();
        $this->get('/users/logout');

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
        $this->assertNull($this->identidadeNaSessao());
    }

    /**
     * Fluxo completo com a sessão de verdade (sem loginAsUser()): login → acessa uma
     * página protegida → logout → a mesma página volta a exigir autenticação.
     *
     * @return void
     */
    public function testFluxoCompletoLoginAcessoLogout(): void
    {
        $credenciais = $this->criarUsuarioParaLogin();

        $this->post('/users/login', [
            'email' => $credenciais['email'],
            'password' => $credenciais['senha'],
            '2fa' => self::codigo2faAtual($credenciais['secret']),
        ]);
        $this->assertRedirect(['controller' => 'Pages', 'action' => 'home']);

        $this->reutilizarSessaoDoUltimoRequest();
        $this->get('/categorias');
        $this->assertResponseOk();

        $this->reutilizarSessaoDoUltimoRequest();
        $this->get('/users/logout');
        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
        $this->assertNull($this->identidadeNaSessao());

        $this->reutilizarSessaoDoUltimoRequest();
        $this->get('/categorias');
        // Nos testes o redirect de não-autenticado é desligado (Application::getAuthenticationService()).
        $this->assertResponseCode(401);
    }

    /**
     * minhaConta não deve funcionar sem autenticação.
     *
     * @return void
     */
    public function testMinhaContaRequiresAuthentication(): void
    {
        $this->get('/users/minha-conta');
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * minhaConta deve exibir os dados do próprio usuário autenticado.
     *
     * @return void
     */
    public function testMinhaContaExibeDadosDoUsuarioAutenticado(): void
    {
        $this->loginAsUser();
        $this->get('/users/minha-conta');

        $this->assertResponseOk();
    }

    /**
     * Um POST em minhaConta sem alterar a senha deve apenas persistir os demais campos.
     *
     * @return void
     */
    public function testMinhaContaAtualizaDadosSemAlterarSenha(): void
    {
        $this->loginAsUser();
        $users = $this->getTableLocator()->get('Users');
        $emailOriginal = $users->get(1)->email;

        $this->post('/users/minha-conta', ['username' => 'usuario_atualizado', 'email' => $emailOriginal]);

        $this->assertResponseOk();
        $recarregado = $users->get(1);
        $this->assertSame('usuario_atualizado', $recarregado->username);
    }

    /**
     * Um POST em minhaConta alterando a senha deve encerrar todas as sessões do
     * usuário e redirecionar para o login (a sessão atual, inclusive).
     *
     * @return void
     */
    public function testMinhaContaAlterandoSenhaEncerraSessoesEExigeNovoLogin(): void
    {
        $this->loginAsUser();
        $users = $this->getTableLocator()->get('Users');
        $original = $users->get(1);

        $this->post('/users/minha-conta', [
            'username' => $original->username,
            'email' => $original->email,
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $this->assertRedirect(['action' => 'login']);
        $sessions = $this->getTableLocator()->get('Sessions');
        $this->assertSame(0, $sessions->find()->where(['user_id' => 1])->count());

        // Só o aviso de novo login: sem o `return` no redirect, a action seguia e somava a
        // mensagem de sucesso.
        $mensagens = $_SESSION['Flash']['flash'] ?? [];
        $this->assertCount(1, $mensagens, 'Só o aviso de novo login deveria aparecer.');
        $this->assertSame('flash/warning', $mensagens[0]['element']);
    }

    /**
     * finalizarSessao não deve funcionar sem autenticação.
     *
     * @return void
     */
    public function testFinalizarSessaoRequiresAuthentication(): void
    {
        $this->post('/users/finalizar-sessao', ['id' => 'nao-importa']);
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * finalizarSessao com um ID que não é um UUID válido deve mostrar um erro e,
     * mesmo assim, redirecionar de volta (nunca deve resultar em erro 500).
     *
     * @return void
     */
    public function testFinalizarSessaoComIdInvalidoMostraErroERedireciona(): void
    {
        $this->loginAsUser();
        $this->post('/users/finalizar-sessao', ['id' => 'nao-e-um-uuid']);

        $this->assertResponseSuccess();
    }

    /**
     * finalizarSessao com o id_secundario de uma sessão existente deve removê-la do
     * banco e redirecionar de volta para minhaConta.
     *
     * @return void
     */
    public function testFinalizarSessaoRemoveSessaoDoBancoERedireciona(): void
    {
        $this->loginAsUser();

        $connection = ConnectionManager::get('test');
        $connection->execute(
            'INSERT INTO sessions (id, user_id, created, modified) VALUES (:id, :user_id, NOW(), NOW())',
            ['id' => 'outra-sessao-para-finalizar', 'user_id' => 1],
        );
        $idSecundario = $connection->execute(
            'SELECT id_secundario FROM sessions WHERE id = :id',
            ['id' => 'outra-sessao-para-finalizar'],
        )->fetch('assoc')['id_secundario'];

        $this->post('/users/finalizar-sessao', ['id' => $idSecundario]);

        $this->assertResponseSuccess();
        $sessions = $this->getTableLocator()->get('Sessions');
        $this->assertFalse($sessions->exists(['id' => 'outra-sessao-para-finalizar']));
    }

    /**
     * geraQrCode2fa não deve funcionar sem autenticação.
     *
     * @return void
     */
    public function testGeraQrCode2faRequiresAuthentication(): void
    {
        $this->post('/users/gera-qr-code2fa', ['idUser' => 1, 'novoQrCode' => false]);
        $this->assertTrue(in_array($this->_response->getStatusCode(), [302, 401]));
    }

    /**
     * geraQrCode2fa deve retornar 400 quando os dados enviados são inválidos.
     *
     * @return void
     */
    public function testGeraQrCode2faComDadosInvalidosRetorna400(): void
    {
        $this->loginAsUser();
        $this->post('/users/gera-qr-code2fa', ['idUser' => 1]);

        $this->assertResponseCode(400);
    }

    /**
     * geraQrCode2fa para o próprio usuário deve retornar o QR Code como data URI
     * (nunca como markup SVG bruto — ver comentário no controller sobre DOM XSS).
     *
     * @return void
     */
    public function testGeraQrCode2faRetornaDataUriParaProprioUsuario(): void
    {
        $this->loginAsUser();
        $this->post('/users/gera-qr-code2fa', ['idUser' => 1, 'novoQrCode' => false]);

        $this->assertResponseOk();
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $this->respostaQrCode2fa()['qrCode']);
    }

    /**
     * geraQrCode2fa deve devolver, em JSON, o QrCode (data URI) e os dados para cadastro
     * manual do 2FA (chave agrupada de 4 em 4 e codinome) que correspondem ao secret persistido.
     *
     * @return void
     */
    public function testGeraQrCode2faRetornaQrCodeEDadosParaCadastroManual(): void
    {
        $this->loginAsUser(['root' => true]);
        $this->post('/users/gera-qr-code2fa', ['idUser' => 1, 'novoQrCode' => 1]);

        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $dados = $this->respostaQrCode2fa();

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $dados['qrCode']);
        $user = $this->getTableLocator()->get('Users')->get(1);
        $this->assertInstanceOf(User::class, $user);
        $secret = $user->descripSecret2FA();
        $this->assertSame($secret, str_replace(' ', '', $dados['chave']));
        $this->assertMatchesRegularExpression('/^([A-Z2-7]{4} )*[A-Z2-7]{1,4}$/', $dados['chave']);
        $this->assertSame('KeyAnyWhere (' . $user->email . ')', $dados['codinome']);
    }

    /**
     * Corpo JSON da resposta do geraQrCode2fa.
     *
     * @return array<string, mixed>
     */
    private function respostaQrCode2fa(): array
    {
        $dados = json_decode($this->_getBodyAsString(), true);
        $this->assertIsArray($dados);

        return $dados;
    }

    /**
     * geraQrCode2fa com novoQrCode=true deve gerar e persistir um novo segredo 2FA.
     *
     * @return void
     */
    public function testGeraQrCode2faComNovoQrCodeGeraNovoSecret(): void
    {
        $this->loginAsUser();
        $users = $this->getTableLocator()->get('Users');
        $secretOriginal = $users->get(1)->tfa_secret;

        $this->post('/users/gera-qr-code2fa', ['idUser' => 1, 'novoQrCode' => true]);

        $this->assertResponseOk();
        $recarregado = $users->get(1);
        $this->assertNotSame($secretOriginal, $recarregado->tfa_secret);
    }

    /**
     * geraQrCode2fa deve negar (401) quando um usuário não-root tenta gerar o
     * QrCode de outro usuário.
     *
     * @return void
     */
    public function testGeraQrCode2faNegaParaOutroUsuarioQuandoNaoRoot(): void
    {
        $this->loginAsNonRootUser();
        $this->post('/users/gera-qr-code2fa', ['idUser' => 1, 'novoQrCode' => false]);

        $this->assertResponseCode(401);
    }

    /**
     * geraQrCode2fa deve permitir que um usuário root gere o QrCode de outro usuário.
     *
     * @return void
     */
    public function testGeraQrCode2faComoRootGeraParaOutroUsuario(): void
    {
        $this->loginAsUser(['root' => true]);
        $users = $this->getTableLocator()->get('Users');
        $outroUsuario = $users->save($users->newEntity([
            'username' => 'usuario_alvo_qrcode',
            'email' => 'usuario_alvo_qrcode@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]));

        $this->post('/users/gera-qr-code2fa', ['idUser' => $outroUsuario->id, 'novoQrCode' => true]);

        $this->assertResponseOk();
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $this->respostaQrCode2fa()['qrCode']);
    }

    /**
     * Com o sistema sem nenhum usuário, configInicial deve exibir o formulário
     * de bootstrap (não redirecionar para o login) — ver
     * UsersTable::configInicialSistema() / UsersController::configInicial().
     *
     * @return void
     */
    public function testConfigInicialSemUsuariosExibeFormulario(): void
    {
        $this->getTableLocator()->get('Users')->deleteAll([]);

        $this->get('/users/config-inicial');

        $this->assertResponseOk();
        // Cabeçalho das duas etapas, no layout config_inicial
        $this->assertResponseContains('Bem-vindo KeyAnyWhere!');
        $this->assertResponseContains('(1 de 2)');
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
     * A criação do primeiro usuário acontece sem nenhuma identidade no request — antes a
     * auditoria era descartada (usuario_id NOT NULL); agora é registrada com usuario_id nulo.
     *
     * @return void
     */
    public function testConfigInicialPostAuditaCriacaoSemUsuarioAutenticado(): void
    {
        $this->getTableLocator()->get('Users')->deleteAll([]);

        $this->enableSecurityToken();
        $this->post('/users/config-inicial', [
            'username' => 'primeiro_root',
            'email' => 'primeiro_root@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $criado = $this->getTableLocator()->get('Users')->find()->where(['username' => 'primeiro_root'])->firstOrFail();
        $registro = $this->getTableLocator()->get('Auditorias')->find()
            ->where(['contexto' => 'user', 'id_entidade' => $criado->get('id')])
            ->first();

        $this->assertNotNull($registro, 'A criação do primeiro usuário deveria ser auditada.');
        $this->assertSame('criacao', $registro->get('acao'));
        $this->assertNull($registro->get('usuario_id'));
    }

    /**
     * Instalação refeita no mesmo domínio (novo SECURITY_SALT): o navegador ainda tem o cookie
     * CSRF antigo. O GET precisa emitir um cookie novo, senão o POST falha com
     * "Missing or invalid CSRF cookie." (ver CsrfProtectionKawMiddleware).
     *
     * @return void
     */
    public function testConfigInicialComCookieCsrfDeOutroSaltRenovaCookieECriaUsuario(): void
    {
        $this->getTableLocator()->get('Users')->deleteAll([]);
        $nomeCookie = Application::nomeCookieCsrf();
        $cookieAntigo = $this->tokenCsrfAssinadoComOutroSalt();

        $this->cookie($nomeCookie, $cookieAntigo);
        $this->get('/users/config-inicial');

        $this->assertResponseOk();
        $this->assertInstanceOf(Response::class, $this->_response);
        $cookieNovo = $this->_response->getCookie($nomeCookie);
        $this->assertNotNull($cookieNovo, 'O GET deveria emitir um cookie CSRF novo no lugar do inválido.');
        $this->assertNotSame($cookieAntigo, $cookieNovo['value']);

        $this->cookie($nomeCookie, $cookieNovo['value']);
        $this->enableSecurityToken();
        $this->post('/users/config-inicial', [
            'username' => 'primeiro_root',
            'email' => 'primeiro_root@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $this->assertRedirect(['controller' => 'Users', 'action' => 'configInicialTfa']);
    }

    /**
     * POST com o cookie antigo continua rejeitado — a renovação acontece no GET, sem
     * afrouxar a validação do POST.
     *
     * @return void
     */
    public function testConfigInicialPostComCookieCsrfDeOutroSaltEhRejeitado(): void
    {
        $this->getTableLocator()->get('Users')->deleteAll([]);

        $this->cookie(Application::nomeCookieCsrf(), $this->tokenCsrfAssinadoComOutroSalt());
        $this->enableSecurityToken();
        $this->post('/users/config-inicial', [
            'username' => 'primeiro_root',
            'email' => 'primeiro_root@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]);

        $this->assertResponseCode(403);
        $this->assertSame(0, $this->getTableLocator()->get('Users')->find()->count());
    }

    /**
     * Token CSRF assinado com outro SECURITY_SALT (o de uma instalação anterior).
     *
     * @return string
     */
    private function tokenCsrfAssinadoComOutroSalt(): string
    {
        $saltAtual = Security::getSalt();
        Security::setSalt(str_repeat('salt-de-outra-instalacao', 4));

        try {
            return (new CsrfProtectionKawMiddleware())->createToken();
        } finally {
            Security::setSalt($saltAtual);
        }
    }

    /**
     * Com o sistema já configurado, configInicial redireciona para o login. O destino já saía
     * certo antes do `return` (o Controller::redirect() mantém o primeiro Location), mas o
     * resto da action rodava; este teste segura o destino se a ordem do código mudar.
     *
     * @return void
     */
    public function testConfigInicialComSistemaConfiguradoRedirecionaParaLogin(): void
    {
        $this->get('/users/config-inicial');

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
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
        $this->assertInstanceOf(User::class, $recarregado);
        $this->assertNotEmpty($recarregado->tfa_secret);

        // Dados para cadastro manual, para quem não conseguir escanear o QrCode
        $chave = trim(chunk_split($recarregado->descripSecret2FA(), 4, ' '));
        $this->assertResponseContains($chave);
        $this->assertResponseContains('KeyAnyWhere (usuario_bootstrap@example.com)');
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

        $this->enableSecurityToken();
        $this->post('/users/config-inicial-tfa', ['id' => $user->id, 'tfa' => true]);

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
        $recarregado = $users->get($user->id);
        $this->assertTrue($recarregado->tfa_ativo);
    }

    /**
     * REGRESSÃO DE SEGURANÇA: configInicialTfa é acessível sem autenticação
     * (ver ACTIONS_SEM_AUTENTICACAO). Quando o sistema JÁ está configurado
     * (2FA ativo), acessá-la não pode regenerar nem expor um novo segredo 2FA
     * do usuário root — deve apenas redirecionar para o login sem tocar no banco.
     *
     * @return void
     */
    public function testConfigInicialTfaNaoRegeneraSecretQuandoSistemaJaConfigurado(): void
    {
        $google2fa = new Google2FA();
        $secretOriginal = $google2fa->generateSecretKey();

        $users = $this->getTableLocator()->get('Users');
        $users->deleteAll([]);
        $user = $users->newEntity([
            'username' => 'usuario_ja_configurado',
            'email' => 'usuario_ja_configurado@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]);
        // 'root' e 'tfa_ativo' são guarded na entidade (não são mass-assignable),
        // por isso são atribuídos diretamente aqui.
        $user->root = true;
        $user->tfa_ativo = true;
        $user->tfa_secret = $secretOriginal;
        $users->save($user);

        $this->get('/users/config-inicial-tfa');

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
        $recarregado = $users->get($user->id);
        $this->assertSame($secretOriginal, $recarregado->descripSecret2FA());
    }

    /**
     * Login com senha errada registra `login_credencial_invalida` no escopo `seguranca`, com o e-mail
     * digitado no contexto (não na mensagem).
     *
     * @return void
     */
    public function testLoginComSenhaIncorretaRegistraEventoDeSeguranca(): void
    {
        $credenciais = $this->criarUsuarioParaLogin();

        $this->post('/users/login', [
            'email' => $credenciais['email'],
            'password' => self::gerarSenhaDeTeste(),
            '2fa' => self::codigo2faAtual($credenciais['secret']),
        ]);

        $linha = $this->logSeguranca(Seguranca::LOGIN_CREDENCIAL_INVALIDA);
        $this->assertSame('notice', $linha['nivel']);
        $this->assertSame($credenciais['email'], $linha['contexto']['email']);
        $this->assertStringNotContainsString($credenciais['email'], $linha['mensagem']);
    }

    /**
     * Login com 2FA errado (fora do modo de desenvolvimento) registra `login_2fa_invalido`.
     *
     * @return void
     */
    public function testLoginComCodigo2faInvalidoRegistraEventoDeSeguranca(): void
    {
        $credenciais = $this->criarUsuarioParaLogin();
        $codigoErrado = str_pad(
            (string)(((int)self::codigo2faAtual($credenciais['secret']) + 500000) % 1000000),
            6,
            '0',
            STR_PAD_LEFT,
        );

        $this->postLoginEmModoProducao([
            'email' => $credenciais['email'],
            'password' => $credenciais['senha'],
            '2fa' => $codigoErrado,
        ]);

        $linha = $this->logSeguranca(Seguranca::LOGIN_2FA_INVALIDO);
        $this->assertSame('notice', $linha['nivel']);
    }

    /**
     * Um usuário comum numa action somente root registra `acesso_somente_root`, com o usuário no bloco
     * `requisicao`.
     *
     * @return void
     */
    public function testNaoRootEmActionSomenteRootRegistraEventoDeSeguranca(): void
    {
        $this->loginAsNonRootUser();
        $this->get('/users/add');

        $this->assertRedirect(['controller' => 'Pages', 'action' => 'home']);
        $linha = $this->logSeguranca(Seguranca::ACESSO_SOMENTE_ROOT);
        $this->assertSame('warning', $linha['nivel']);
        $this->assertSame('Users::add', $linha['requisicao']['rota']);
    }

    /**
     * Ouve um evento no gerenciador global (como um plugin faria) e devolve a lista de disparos.
     *
     * @param string $nome
     * @return \ArrayObject<int, \Cake\Event\EventInterface<object>>
     */
    private function ouvirEvento(string $nome): ArrayObject
    {
        $disparos = new ArrayObject();
        $ouvinte = function (EventInterface $event) use ($disparos): void {
            $disparos->append($event);
        };
        EventManager::instance()->on($nome, $ouvinte);
        $this->ouvintes[] = [$nome, $ouvinte];

        return $disparos;
    }

    /**
     * @var array<int, array{string, callable}>
     */
    private array $ouvintes = [];

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        foreach ($this->ouvintes as [$nome, $ouvinte]) {
            EventManager::instance()->off($nome, $ouvinte);
        }
        $this->ouvintes = [];
        parent::tearDown();
    }

    /**
     * As duas etapas da configuração inicial disparam eventos para os plugins; com o sistema já
     * configurado, nenhum dispara.
     *
     * @return void
     */
    public function testConfigInicialDisparaEventosDasEtapas(): void
    {
        $inicio = $this->ouvirEvento(UsersController::EVENTO_CONFIG_INICIAL_INICIO);
        $segundoFator = $this->ouvirEvento(UsersController::EVENTO_CONFIG_INICIAL_SEGUNDO_FATOR);

        // Sistema configurado (fixture): nenhum evento
        $this->get('/users/config-inicial');
        $this->get('/users/config-inicial-tfa');
        $this->assertCount(0, $inicio);
        $this->assertCount(0, $segundoFator);

        $users = $this->getTableLocator()->get('Users');
        $users->deleteAll([]);
        $this->get('/users/config-inicial');
        $this->assertResponseOk();
        $this->assertCount(1, $inicio);
        $this->assertCount(0, $segundoFator);

        $criado = $users->saveOrFail($users->newEntity([
            'username' => 'primeiro_root',
            'email' => 'primeiro_root@example.com',
            'password' => self::gerarSenhaDeTeste(),
        ]));
        $this->get('/users/config-inicial-tfa');
        $this->assertResponseOk();
        $this->assertCount(1, $segundoFator);
        $evento = $segundoFator[0];
        $this->assertInstanceOf(EventInterface::class, $evento);
        $this->assertSame($criado->get('id'), $evento->getData('user')->id);
    }

    /**
     * Elements listados em `Kaw.Login.elementos` aparecem abaixo do formulário de login.
     *
     * @return void
     */
    public function testLoginRenderizaElementsConfigurados(): void
    {
        $this->get('/users/login');
        $this->assertResponseNotContains('A sessão expirou');

        Configure::write('Kaw.Login.elementos', ['Users/sessaoExpirada']);
        $this->get('/users/login');

        $this->assertResponseOk();
        $this->assertResponseContains('A sessão expirou');
    }

    /**
     * Itens de `Kaw.Menu.configuracoes` (só root) e `Kaw.Menu.outros` (com `somenteRoot`) no menu.
     *
     * @return void
     */
    public function testMenuComItensConfigurados(): void
    {
        Configure::write('Kaw.Menu', [
            'configuracoes' => [
                ['url' => '/item-de-configuracao', 'icone' => 'bi-sliders', 'rotulo' => 'Item de configuração'],
            ],
            'outros' => [
                ['url' => '/item-para-todos', 'icone' => 'bi-headset', 'rotulo' => 'Item para todos'],
                ['url' => '/item-so-root', 'rotulo' => 'Item só do root', 'somenteRoot' => true],
            ],
        ]);

        try {
            $this->loginAsUser(['root' => true]);
            $this->get('/users/minha-conta');
            $this->assertResponseContains('href="/item-de-configuracao"');
            $this->assertResponseContains('Item para todos');
            $this->assertResponseContains('Item só do root');
            $this->assertResponseContains('Outros');

            $this->loginAsNonRootUser();
            $this->get('/users/minha-conta');
            $this->assertResponseNotContains('Item de configuração');
            $this->assertResponseContains('Item para todos');
            $this->assertResponseNotContains('Item só do root');
        } finally {
            Configure::write('Kaw.Menu', ['configuracoes' => [], 'outros' => []]);
        }

        // Sem itens, a seção "Outros" não aparece
        $this->get('/users/minha-conta');
        $this->assertResponseNotContains('dropdown-header">Outros');
    }
}
