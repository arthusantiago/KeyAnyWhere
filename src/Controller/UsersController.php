<?php
declare(strict_types=1);

namespace App\Controller;

use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use App\Model\Entity\User;
use App\Model\Table\IpsBloqueadosTable;
use App\Log\GerenciadorEventos;
use Authentication\Authenticator\ResultInterface;
use Cake\Validation\Validator;
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
        'configInicialTfa'
    ];

    private const CREDENCIAL_LOGIN_INCORRETO = [
        ResultInterface::FAILURE_IDENTITY_NOT_FOUND,
        ResultInterface::FAILURE_CREDENTIALS_INVALID,
    ];

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        // desabilitando o cache por segurança.
        $this->response = $this->response->withDisabledCache();
        $this->Authentication->addUnauthenticatedActions(self::ACTIONS_SEM_AUTENTICACAO);
        $this->FormProtection->setConfig('unlockedActions', ['geraQrCode2fa']);

        $caminho = array_values(array_filter(explode('/', $this->request->getPath())));
        if (count($caminho) == 1) {
            // O CakePHP mapeia '/users/' para '/users/index' mas não adiciona no request->getPath() a action 'index'
            array_push($caminho, 'index');
        }

        if (array_search($caminho[1], self::SOMENTE_ROOT_ACESSA) !== false) {
            $user = $this->Authentication->getResult()->getData();
            if ($user->root == false) {
                $this->Flash->error('Você não tem permissão');
                GerenciadorEventos::notificarEvento(['evento' => 'C2-1', 'request' => $this->request, 'usuario' => $user]);
                return $this->redirect(['controller' => 'Pages', 'action' => 'home']);
            }
        }
    }

    private function ipEstaBloqueado(): bool
    {
        $ipBloqueado = (new IpsBloqueadosTable)
        ->find()
        ->where(['ip' => $this->request->clientIp()])
        ->limit(1)
        ->toArray();

        return empty($ipBloqueado) ? false : true ;
    }

    /**
     * Método que verifica se é preciso executar a configuração inicial do sistema.
     *
     * @access	private
     * @return	mixed
     */
    private function executarConfigIncial(): bool
    {
        $user = $this->Users
            ->find()
            ->orderAsc('id')
            ->limit(1)
            ->first();

        $result = false;

        if (empty($user)) {
            $result = true;
        }

        if (empty($user) == false && $user->tfa_ativo == false) {
            $result = true;
        }

        return $result;
    }

    /**
     * Metodo que inicia a Configuração Inicial da ferramenta.
     * Esse processo deve ser executado ao acessar o KAW pela primeira vez.
     *
     * @access	public
     * @return	void
     */
    public function configInicial()
    {
        if ($this->executarConfigIncial() == false) {
            $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $user = $this->Users
            ->find()
            ->orderDesc('id')
            ->limit(1)
            ->first();

        if (empty($user) == false && $user->email) {
            return $this->redirect(['controller' => 'Users', 'action' => 'configInicialTfa']);
        }

        if ($this->request->is('post')) {
            $user = $this->Users->newEmptyEntity();
            $user = $this->Users->patchEntity($user, $this->request->getData());
            $user->root = true;

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Usuário criado com sucesso!'));
                return $this->redirect(['controller' => 'Users', 'action' => 'configInicialTfa']);
            }else{
                $this->Flash->error('Erro ao salvar', ['params' => ['mensagens' => $user->getErrors()]]);
            }
        }

        $this->viewBuilder()->setLayout('config_inicial');
    }

    /**
     * Metodo que gerencia a configuração do 2FA no processo de Configuração Inicial
     *
     * @access	public
     * @return	void
     */
    public function configInicialTfa()
    {
        if ($this->executarConfigIncial() == false) {
            $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $user = $this->Users
            ->find()
            ->orderDesc('id')
            ->limit(1)
            ->first();

        if (empty($user)) {
            return $this->redirect(['controller' => 'Users', 'action' => 'configInicial']);
        }

        if ($this->request->is('post')) {
            $user = $this->Users->get($this->request->getData('id'));
            $user->tfa_ativo = $this->request->getData('tfa');
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Usuário configurado com sucesso!'));
                return $this->redirect(['controller' => 'Users', 'action' => 'login']);
            }else{
                $this->Flash->error('Erro ao salvar', ['params' => ['mensagens' => $user->getErrors()]]);
            }
        }

        $user->tfa_secret = $user->geraSecret2FA();
        if ($this->Users->save($user) == false) {
            $this->Flash->error('Erro ao salvar', ['params' => ['mensagens' => $user->getErrors()]]);
        }

        $g2faUrl = (new Google2FA())->getQRCodeUrl(
            'KeyAnyWhere',
            $user->email,
            $user->descripSecret2FA()
        );

        $render = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );

        $strSvgQrCode = (new Writer($render))->writeString($g2faUrl);

        $this->viewBuilder()->setLayout('config_inicial');
        $this->set(compact('user', 'strSvgQrCode'));
    }

    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $this->viewBuilder()->setLayout('login');

        if ($this->executarConfigIncial()) {
            $this->redirect(['controller' => 'Users', 'action' => 'configInicial']);
        }

        if (
            $this->ipEstaBloqueado() == false
            && $this->request->is('post')
        ) {
            $resultLogin = $this->Authentication->getResult();
            $tfaValido = false;
            if ($resultLogin->getData() !== null) {
                $tfaValido = $resultLogin->getData()->valida2fa($this->request->getData('2fa'));
            }

            if (
                $resultLogin->isValid()
                && $tfaValido
            ) {
                return $this->redirect(['controller' => 'Pages', 'action' => 'home']);
            } else if ($resultLogin->isValid() == false) {
                $this->Flash->error('Usuário ou senha inválido');
                if (array_search($resultLogin->getStatus(), $this::CREDENCIAL_LOGIN_INCORRETO) !== false) {
                    GerenciadorEventos::notificarEvento([
                        'evento' => 'C1-1',
                        'request' => $this->request,
                        'usuario' => [
                            'dados' => ['e-mail' => $this->request->getData('email')],
                            'texto' => 'Credenciais utilizadas para logar: '
                        ]
                    ]);
                }
            } else if ($resultLogin->isValid() && $tfaValido == false) {
                $this->Flash->error('Segundo Fator de Autenticação incorreto');
                GerenciadorEventos::notificarEvento([
                    'evento' => 'C1-2',
                    'request' => $this->request,
                    'usuario' => [
                        'dados' => ['e-mail' => $this->request->getData('email')],
                        'texto' => 'Credenciais utilizadas para logar: '
                    ]
                ]);
            }
        }

        if ($this->ipEstaBloqueado()) {
            $this->Flash->error('Seu IP foi bloqueado por inúmeras tentativas erradas de login');
        }

        $this->Authentication->logout();
    }

    public function logout()
    {
        $result = $this->Authentication->getResult();
        // regardless of POST or GET, redirect if user is logged in
        if ($result->isValid()) {
            $this->Authentication->logout();
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
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
            $this->Users,
            [
                'limit' => 10,
                'order' => [
                    'Users.username' => 'asc'
                ]
            ]
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
                $this->Flash->success(__('Salvo com sucesso'));

                return $this->redirect(['action' => 'edit', $user->id]);
            }
            $this->Flash->error(
                __('Erro ao salvar o usuário.'),
                ['params' => ['mensagens' => $user->getErrors()]]
            );
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
    public function edit($id = null)
    {
        $user = $this->Users->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, array_filter($this->request->getData()));
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Salvo com sucesso'));
            } else {
                $this->Flash->error(__('Erro ao salvar.'),  ['params' => ['mensagens' => $user->getErrors()]]);
            }
        }

        $this->viewBuilder()->setLayout('administrativo');
        $this->set(compact('user'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('Excluído com sucesso'));
        } else {
            $this->Flash->error(null, ['params' => ['mensagens' => $user->getErrors()]]);
        }

        return $this->redirect(['action' => 'index']);
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

        if ($this->request->is(['patch', 'post', 'put']))
        {
            $user = $this->Users->patchEntity($user, array_filter($this->request->getData()));

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Salvo com sucesso'));
            }else{
                $this->Flash->error(null, ['params' => ['mensagens' => $user->getErrors()]]);
            }

            return $this->redirect(['action' => 'minhaConta']);
        }
        $this->viewBuilder()->setLayout('administrativo');
        $this->set(compact('user'));
    }

    public function geraQrCode2fa()
    {
        $this->request->allowMethod(['post']);
        $request = $this->request->getParsedBody();
        $validator = new Validator();

        $validator
            ->requirePresence('idUser', true, 'O ID do usuário não foi informado')
            ->notEmptyString('idUser', 'O ID do usuário não pode estar vazio')
            ->integer('idUser', 'O ID do usuário precisa ser um inteiro')
            ->requirePresence('novoQrCode', true, "A propriedade 'novoQrCode' não foi informada")
            ->notEmptyString('novoQrCode', "A propriedade 'novoQrCode' não pode estar vazia")
            ->boolean('novoQrCode', "A propriedade 'novoQrCode' precisa ser um boolean");

        $erros = $validator->validate($request);

        if ($erros) {
            return $this->response
                ->withType('application/json; charset=UTF-8')
                ->withStatus(400, 'Dados invalidos enviados ao servidor')
                ->withStringBody(json_encode($erros));
        }

        $user = $this->Authentication->getResult()->getData();

        if ($request['idUser'] !=  $user->id) {
            if ($user->root) {
                $user = $this->Users->get($request['idUser']);
            } else {
                GerenciadorEventos::notificarEvento(['evento' => 'C2-1', 'request' => $this->request, 'usuario' => $user]);
                return $this->response
                    ->withType('application/json; charset=UTF-8')
                    ->withStatus(401, 'Você não tem permissão');
            }
        }

        if ($request['novoQrCode']) {
            $user = $this->geraNovoSecret2FA($user->id);
        }

        $g2faUrl = (new Google2FA())->getQRCodeUrl(
            'KeyAnyWhere',
            $user->email,
            $user->descripSecret2FA()
        );

        $render = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );

        $strSvgQrCode = (new Writer($render))->writeString($g2faUrl);

        return $this->response
            ->withType('text/html; charset=UTF-8')
            ->withStringBody($strSvgQrCode);
    }

    /**
     * geraNovoSecret2FA.
     *
     * @access	private
     * @param int $idUser ID de um usuário especifico.
     * @return	App\Model\Entity\User
     */
    private function geraNovoSecret2FA(int $idUser): User
    {
        $user = $this->Users->get($idUser);
        $user->tfa_secret = $user->geraSecret2FA();
        $this->Users->save($user);
        return $user;
    }
}
