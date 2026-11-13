<?php
declare(strict_types=1);

namespace App\Controller;

use App\I18n\Regional;
use App\Log\Seguranca;
use App\Model\Entity\User;
use App\Service\AutorizadorConfigInicialInterface;
use App\Service\DadosCadastro2fa;
use Authentication\Authenticator\ResultInterface;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\I18n;
use function Cake\I18n\__;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    /* Actions que somente o usuário root pode acessar */
    private const SOMENTE_ROOT_ACESSA = [
        'index',
        'add',
        'edit',
        'delete',
    ];

    private const ACTIONS_SEM_AUTENTICACAO = [
        'login',
        'configInicial',
        'configInicialTfa',
    ];

    private const CREDENCIAL_LOGIN_INCORRETO = [
        ResultInterface::FAILURE_IDENTITY_NOT_FOUND,
        ResultInterface::FAILURE_CREDENTIALS_INVALID,
    ];

    /**
     * Evento disparado ao abrir a primeira etapa da configuração inicial (criação do primeiro
     * usuário), enquanto o sistema não está configurado. Pode disparar mais de uma vez (a cada
     * acesso à etapa): quem ouve precisa ser idempotente. Uso típico: um plugin preparar o que a
     * instalação precisa antes do primeiro login.
     */
    public const EVENTO_CONFIG_INICIAL_INICIO = 'Kaw.ConfigInicial.inicio';

    /**
     * Evento disparado ao abrir a segunda etapa da configuração inicial (ativação do 2FA), já com o
     * primeiro usuário criado (`$event->getData('user')`). Também pode disparar mais de uma vez.
     */
    public const EVENTO_CONFIG_INICIAL_SEGUNDO_FATOR = 'Kaw.ConfigInicial.segundoFator';

    /**
     * beforeFilter callback.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        // desabilitando o cache por segurança.
        $this->response = $this->response->withDisabledCache();
        $this->Authentication->addUnauthenticatedActions(self::ACTIONS_SEM_AUTENTICACAO);
        $this->FormProtection->setConfig('unlockedActions', ['geraQrCode2fa', 'finalizarSessao', 'delete']);

        $caminho = $this->normalizaRecurso();

        if (array_search($caminho[1], self::SOMENTE_ROOT_ACESSA) !== false) {
            $user = $this->Authentication->getResult()?->getData();
            // Sem usuário autenticado não há o que checar aqui: o AuthenticationComponent
            // barra a requisição no Controller.startup (logo após este beforeFilter).
            $ehRoot = $user instanceof User && $user->root;
            if ($user !== null && !$ehRoot) {
                $this->Flash->error('Você não tem permissão');
                Seguranca::registrar(
                    'warning',
                    Seguranca::ACESSO_SOMENTE_ROOT,
                    'O usuário tentou acessar um recurso que somente o root tem permissão',
                );

                // CakePHP 5.2+: o redirect vai no resultado do evento, não no retorno do callback
                $event->setResult($this->redirect(['controller' => 'Pages', 'action' => 'home']));
                $event->stopPropagation();
            }
        }
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $users = $this->paginate(
            $this->Users->find()->where(['root' => false]),
            [
                'limit' => 10,
                'order' => ['Users.username' => 'asc'],
            ],
        );

        $this->viewBuilder()->setLayout('administrativo');
        $this->set(compact('users'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            $user->tfa_secret = $user->geraSecret2FA();
            if ($this->Users->save($user)) {
                $this->Flash->success('');

                return $this->redirect(['action' => 'edit', $user->id]);
            }
            $this->Flash->error('', ['params' => ['mensagens' => $user->getErrors()]]);
        }

        $this->viewBuilder()->setLayout('administrativo');
        $this->set(compact('user'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $user = $this->Users->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, array_filter($this->request->getData()));
            $senhaAlterada = $user->isDirty('password');
            if ($this->Users->save($user)) {
                if ($senhaAlterada) {
                    if ($this->finalizarTodasSessoes($user->id)) {
                        $this->Flash->warning('Como a senha foi alterada, todas as suas sessões foram encerradas');
                    }
                }

                $this->Flash->success('');
            } else {
                $this->Flash->error('', ['params' => ['mensagens' => $user->getErrors()]]);
            }
        }

        $sessions = $this->Users->Sessions
            ->find()
            ->where(['user_id' => $user->id])
            ->orderByAsc('created');

        $this->viewBuilder()->setLayout('administrativo');
        $this->set(compact('user', 'sessions'));
    }

    /**
     * Delete method
     *
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete()
    {
        $this->request->allowMethod(['post', 'delete']);
        $id = $this->request->getData('id');
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success('Excluído com sucesso');
        } else {
            $this->Flash->error('', ['params' => ['mensagens' => $user->getErrors()]]);
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Método que executa o processo de login no sistema.
     *
     * @access public
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $this->viewBuilder()->setLayout('login');

        if ($this->Users->configInicialSistema()) {
            return $this->redirect(['controller' => 'Users', 'action' => 'configInicial']);
        }

        if ($this->request->is('post')) {
            // O campo pode faltar ou vir como array num POST forjado — tratamos como código
            // vazio (inválido), para cair no fluxo normal de falha (C1-2) em vez de um TypeError.
            $codigoTfa = $this->request->getData('2fa');
            if (
                $this->validarUsarioSenha()
                && $this->validarTFA(is_string($codigoTfa) ? $codigoTfa : '')
            ) {
                return $this->redirect(['controller' => 'Pages', 'action' => 'home']);
            }

            $this->Flash->error('Credenciais de acesso incorretas');
        }

        $this->Authentication->logout();
    }

    /**
     * Executa o processo de logout do sistema
     *
     * @access public
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function logout()
    {
        $result = $this->Authentication->getResult();

        if ($result->isValid()) {
            $this->Authentication->logout();

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
    }

    /**
     * Método Minha Conta
     *
     * @return \Cake\Http\Response|null|void Redireciona na adição bem-sucedida ou renderiza a view caso contrário.
     */
    public function minhaConta()
    {
        $userAutenticado = $this->Authentication->getResult()->getData();
        $user = $this->Users->get($userAutenticado->id);

        if ($this->request->is(['post', 'put'])) {
            $user = $this->Users->patchEntity($user, array_filter($this->request->getData()));
            $senhaAlterada = $user->isDirty('password');
            if ($this->Users->save($user)) {
                if ($senhaAlterada) {
                    $this->finalizarTodasSessoes();
                    $this->Flash->warning('Como sua senha foi alterada, você precisa logar novamente');

                    return $this->redirect(['action' => 'login']);
                }

                $this->Flash->success('');
            } else {
                $this->Flash->error('', ['params' => ['mensagens' => $user->getErrors()]]);
            }
        }

        $sessions = $this->Users->Sessions
            ->find()
            ->where(['user_id' => $user->id])
            ->orderByAsc('created');

        foreach ($sessions as $session) {
            if ($this->request->getSession()->id() == $session->id) {
                $session->esteDispositivo = true;
            }
        }

        $this->viewBuilder()->setLayout('administrativo');
        $this->set(compact('user', 'sessions'));
    }

    /**
     * Finaliza a sessão informada
     *
     * @access public
     * @return \Cake\Http\Response|null
     */
    public function finalizarSessao()
    {
        $this->request->allowMethod(['post', 'patch']);
        $idSession = $this->request->getData('id');
        $erros = $this->Users->Sessions
            ->getValidator('idSession')
            ->validate(['id' => $idSession], false);

        if ($erros) {
            $this->Flash->error('', ['params' => ['mensagens' => $erros]]);
        } else {
            if ($this->Users->Sessions->deleteAll(['id_secundario' => $idSession])) {
                $this->Flash->success('Sessão finalizada');
            } else {
                $this->Flash->error('A sessão não foi localizada para exclusão');
            }
        }

        return $this->redirect($this->referer(['action' => 'minhaConta']));
    }

    /**
     * Finaliza todas as sessões de um único usuário
     *
     * @access private
     * @param int $idUser Default: null
     * @return mixed
     */
    private function finalizarTodasSessoes(?int $idUser = null): bool
    {
        if ($idUser) {
            $user = $this->Users->get($idUser);
        } else {
            $user = $this->Authentication->getResult()->getData();
        }

        $qtdDeletado = $this->Users->Sessions->deleteAll(['user_id' => $user->id]);

        return $qtdDeletado ? true : false;
    }

    /**
     * Gera o QRCode do Segundo Fator de Autenticação (Two Factor Authentication)
     *
     * @access public
     * @return \Cake\Http\Response|null|void
     */
    public function geraQrCode2fa()
    {
        $this->request->allowMethod(['post']);
        $request = $this->request->getParsedBody();
        $erros = $this->Users
            ->getValidator('qrCode2fa')
            ->validate($request, false);

        if ($erros) {
            return $this->response
                ->withType('application/json; charset=UTF-8')
                ->withStatus(400, 'Dados invalidos enviados ao servidor')
                ->withStringBody(json_encode($erros));
        }

        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getResult()->getData();

        // Somente o usuário root pode gerar o QrCode para outro usuário.
        if ($request['idUser'] != $user->id) {
            if ($user->root) {
                $user = $this->Users->get($request['idUser']);
            } else {
                Seguranca::registrar(
                    'warning',
                    Seguranca::ACESSO_SOMENTE_ROOT,
                    'O usuário tentou acessar um recurso que somente o root tem permissão',
                );

                return $this->response
                    ->withType('application/json; charset=UTF-8')
                    ->withStatus(401, 'Você não tem permissão');
            }
        }

        if ($request['novoQrCode']) {
            $user = $this->geraNovoSecret2FA($user->id);
        }

        $dados2fa = DadosCadastro2fa::doUsuario($user, 300);

        // QrCode retornado como data URI (não como markup SVG bruto): o cliente insere isso via
        // um <img src="..."> em vez de innerHTML, o que evita expor um sink de DOM XSS.
        return $this->response
            ->withType('application/json; charset=UTF-8')
            ->withStringBody((string)json_encode([
                'qrCode' => 'data:image/svg+xml;base64,' . base64_encode($dados2fa['svgQrCode']),
                'chave' => $dados2fa['chave'],
                'codinome' => $dados2fa['codinome'],
            ]));
    }

    /**
     * geraNovoSecret2FA.
     *
     * @access private
     * @param int $idUser ID de um usuário especifico.
     * @return \App\Controller\App\Model\Entity\User
     */
    private function geraNovoSecret2FA(int $idUser): User
    {
        $user = $this->Users->get($idUser);
        $user->tfa_secret = $user->geraSecret2FA();
        $this->Users->save($user);

        return $user;
    }

    /**
     * Verifica se o usuário está autenticado. Se ele forneceu o usuário e senha corretos.
     *
     * @access private
     * @return bool
     */
    private function validarUsarioSenha(): bool
    {
        $resultLogin = $this->Authentication->getResult();
        $usuarioSenhaCorreto = $resultLogin->isValid();

        if ($usuarioSenhaCorreto == false) {
            if (array_search($resultLogin->getStatus(), $this::CREDENCIAL_LOGIN_INCORRETO) !== false) {
                Seguranca::registrar(
                    'notice',
                    Seguranca::LOGIN_CREDENCIAL_INVALIDA,
                    'Durante o login o usuário errou o usuário ou a senha',
                    ['email' => $this->request->getData('email')],
                );
            }
        }

        return $usuarioSenhaCorreto;
    }

    /**
     * Verifica se o código do Segundo Fator de Autenticação (Two Factor Authentication) está correto.
     *
     * @access private
     * @param string $codigoTFA Código de 6 dígitos para verificação
     * @return bool
     */
    private function validarTFA(string $codigoTFA): bool
    {
        $tfaValido = $this->Authentication
            ->getResult()
            ->getData()
            ->valida2fa($codigoTFA);

        if ($tfaValido == false) {
            Seguranca::registrar(
                'notice',
                Seguranca::LOGIN_2FA_INVALIDO,
                'Durante o login o usuário errou o código do 2FA',
                ['email' => $this->request->getData('email')],
            );
        }

        return $tfaValido;
    }

    /**
     * Resposta da configuração inicial recusada pelo AutorizadorConfigInicialInterface: redireciona para
     * `Kaw.ConfigInicial.urlRecusa` quando a instalação a define (ex.: uma página que explica como obter
     * o acesso) e, sem ela, responde 403 em texto.
     *
     * Nunca devolve nulo: a action não pode seguir para a renderização da etapa recusada, nem quando um
     * ouvinte do `Controller.beforeRedirect` cancela o redirect (aí vale o 403).
     *
     * @return \Cake\Http\Response
     */
    private function recusaConfigInicial(): Response
    {
        $urlRecusa = Configure::read('Kaw.ConfigInicial.urlRecusa');
        if ((is_string($urlRecusa) && $urlRecusa !== '') || (is_array($urlRecusa) && $urlRecusa !== [])) {
            $redirect = $this->redirect($urlRecusa);
            if ($redirect !== null) {
                return $redirect;
            }
        }

        return $this->response
            ->withStatus(403)
            ->withStringBody(__('A configuração inicial não foi autorizada.'));
    }

    /**
     * Metodo que inicia a Configuração Inicial da ferramenta.
     * Esse processo deve ser executado ao acessar o KAW pela primeira vez.
     *
     * @access public
     * @param \App\Service\AutorizadorConfigInicialInterface $autorizador Injetado pelo container.
     * @return \Cake\Http\Response|null|void
     */
    public function configInicial(AutorizadorConfigInicialInterface $autorizador)
    {
        if ($this->Users->configInicialSistema() == false) {
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        if (!$autorizador->autoriza($this->request)) {
            return $this->recusaConfigInicial();
        }

        // Antes do primeiro usuário ainda não há idioma escolhido: vale o do navegador.
        I18n::setLocale(Regional::idiomaDoNavegador($this->request));

        $user = $this->Users
            ->find()
            ->orderByDesc('id')
            ->first();

        if (empty($user) == false && $user->email) {
            return $this->redirect(['controller' => 'Users', 'action' => 'configInicialTfa']);
        }

        $this->dispatchEvent(self::EVENTO_CONFIG_INICIAL_INICIO);

        if ($this->request->is('post')) {
            $user = $this->Users->newEmptyEntity();
            $user = $this->Users->patchEntity($user, $this->request->getData());
            $user->root = true;

            if ($this->Users->save($user)) {
                $this->Flash->success('');

                return $this->redirect(['controller' => 'Users', 'action' => 'configInicialTfa']);
            } else {
                $this->Flash->error('', ['params' => ['mensagens' => $user->getErrors()]]);
            }
        }

        $this->viewBuilder()->setLayout('config_inicial');
    }

    /**
     * Metodo que gerencia a configuração do 2FA no processo de Configuração Inicial
     *
     * Acessível sem login (ACTIONS_SEM_AUTENTICACAO): com o sistema já configurado, o `return`
     * do redirect é o que impede um visitante anônimo de gravar um tfa_secret novo para o
     * último usuário.
     *
     * @access public
     * @param \App\Service\AutorizadorConfigInicialInterface $autorizador Injetado pelo container.
     * @return \Cake\Http\Response|null|void
     */
    public function configInicialTfa(AutorizadorConfigInicialInterface $autorizador)
    {
        if ($this->Users->configInicialSistema() == false) {
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        if (!$autorizador->autoriza($this->request)) {
            return $this->recusaConfigInicial();
        }

        // Antes do primeiro usuário ainda não há idioma escolhido: vale o do navegador.
        I18n::setLocale(Regional::idiomaDoNavegador($this->request));

        /** @var \App\Model\Entity\User $user */
        $user = $this->Users
            ->find()
            ->orderByDesc('id')
            ->first();

        if (empty($user)) {
            return $this->redirect(['controller' => 'Users', 'action' => 'configInicial']);
        }

        $this->dispatchEvent(self::EVENTO_CONFIG_INICIAL_SEGUNDO_FATOR, ['user' => $user]);

        if ($this->request->is('post')) {
            $user = $this->Users->get($this->request->getData('id'));
            $user->tfa_ativo = $this->request->getData('tfa');
            if ($this->Users->save($user)) {
                $this->Flash->success('Usuário configurado com sucesso');

                return $this->redirect(['controller' => 'Users', 'action' => 'login']);
            } else {
                $this->Flash->error('', ['params' => ['mensagens' => $user->getErrors()]]);
            }
        }

        $user->tfa_secret = $user->geraSecret2FA();
        if ($this->Users->save($user) == false) {
            $this->Flash->error('', ['params' => ['mensagens' => $user->getErrors()]]);
        }

        $dados2fa = DadosCadastro2fa::doUsuario($user, 400);

        $this->viewBuilder()->setLayout('config_inicial');
        $this->set([
            'user' => $user,
            'strSvgQrCode' => $dados2fa['svgQrCode'],
            'chave2fa' => $dados2fa['chave'],
            'codinome2fa' => $dados2fa['codinome'],
        ]);
    }
}
