<?php
declare(strict_types=1);

namespace App\Controller;

use App\Log\GerenciadorEventos;
use App\Model\Entity\User;
use Cake\Event\EventInterface;

/**
 * Auditorias Controller
 *
 * Consulta somente leitura da trilha de auditoria de negócio (quem fez o quê, quando e
 * em quais campos). Os registros são criados só pelo AuditoriaBehavior, nunca por esta
 * controller. Todas as actions são restritas a usuários root.
 *
 * @property \App\Model\Table\AuditoriasTable $Auditorias
 */
class AuditoriasController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('administrativo');
    }

    /**
     * beforeFilter callback.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $user = $this->Authentication->getResult()?->getData();
        // Sem usuário autenticado não há o que checar aqui: o AuthenticationComponent
        // barra a requisição no Controller.startup (logo após este beforeFilter).
        $ehRoot = $user instanceof User && $user->root;
        if ($user !== null && !$ehRoot) {
            $this->Flash->error('Você não tem permissão');
            GerenciadorEventos::notificarEvento([
                'evento' => 'C2-1',
                'request' => $this->request,
                'usuario' => $user,
            ]);

            $event->setResult($this->redirect(['controller' => 'Pages', 'action' => 'home']));
            $event->stopPropagation();
        }
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $auditorias = $this->paginate(
            $this->Auditorias->find()->contain(['Users']),
            [
                'order' => ['Auditorias.created' => 'desc', 'Auditorias.id' => 'desc'],
                'sortableFields' => ['contexto', 'acao', 'created'],
                'limit' => 30,
            ],
        );

        $this->set(compact('auditorias'));
    }

    /**
     * View method
     *
     * @param string|null $id Auditoria id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $auditoria = $this->Auditorias->get($id, contain: ['Users']);

        $this->set(compact('auditoria'));
    }
}
