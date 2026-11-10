<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Application;
use App\Log\Seguranca;
use App\Model\Entity\User;
use App\Test\TestCase\Log\CapturaLogSegurancaTrait;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
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
        $this->assertStringStartsWith('data:image/svg+xml;base64,', (string)$this->_response->getBody());
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
        $this->assertStringStartsWith('data:image/svg+xml;base64,', (string)$this->_response->getBody());
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
}
