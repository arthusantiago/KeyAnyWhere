<?php
declare(strict_types=1);

namespace App\Controller;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use App\Model\Entity\User;

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

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(self::ACTIONS_SEM_AUTENTICACAO);
        $caminho = array_values(array_filter(explode('/', $this->request->getPath())));
        if (count($caminho) == 1) {
            // O CakePHP mapeia '/users/' para '/users/index' mas não adiciona no request->getPath() a action 'index'
            array_push($caminho, 'index');
        }

        if (array_search($caminho[1], self::SOMENTE_ROOT_ACESSA) !== false) {
            $user = $this->Authentication->getResult()->getData();
            if ($user->root == false) {
                $this->Flash->error('Você não tem permissão');
                return $this->redirect(['controller' => 'Pages', 'action' => 'home']);
            }
        }
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

        $this->viewBuilder()->setLayout('layout_vazio');
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

        $this->viewBuilder()->setLayout('layout_vazio');
        $this->set(compact('user', 'strSvgQrCode'));
    }

    public function login()
    {
        if ($this->executarConfigIncial()) {
            $this->redirect(['controller' => 'Users', 'action' => 'configInicial']);
        }

        $this->request->allowMethod(['get', 'post']);
        $resultLogin = $this->Authentication->getResult();
        $userLogged = $resultLogin->getData();

        if (
            $resultLogin->isValid()
            && $userLogged->valida2fa($this->request->getData('2fa'))
        ) {
            return $this->redirect(['controller' => 'Pages', 'action' => 'home']);
        }

        $this->Authentication->logout();

        $this->viewBuilder()->setLayout('layout_vazio');
        if ($this->request->getData()) {
            $this->Flash->error(__('Usuário, senha ou 2FA inválido', ));
        }
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
                'limit' => 20,
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
            $user = $this->Users->patchEntity($user, $this->request->getData());
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
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
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
            $user = $this->Users->patchEntity($user, $this->request->getData());

            if($this->request->getData('password'))
            {
                $user->password = $this->request->getData('password');
            }

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Salvo com sucesso'));
            }else{
                $this->Flash->error(null, ['params' => ['mensagens' => $user->getErrors()]]);
            }

            return $this->redirect(['action' => 'minhaConta']);
        }
        $this->set(compact('user'));
    }

    public function geraQrCode2fa()
    {
        // por padrão manipulando o usuário logado
        $user = $this->Authentication->getResult()->getData();
        $params = $this->request->getParam('?');

        // manipulando outro usuário
        if (isset($params['idUser']) && $user->root) {
            $user = $this->Users->get($params['idUser']);
        }

        if (isset($params['novoQrCode']) && $params['novoQrCode'] == '1') {
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

        $this->viewBuilder()->setLayout('layout_vazio');
        $this->viewBuilder()->setTemplate('qr_code_2fa');
        $this->set(compact('strSvgQrCode'));
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
