<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Auditorias Controller
 *
 * Consulta somente leitura da trilha de auditoria de negócio (quem fez o quê, quando e
 * em quais campos). Os registros são criados só pelo AuditoriaBehavior, nunca por esta
 * controller. Aberta a todos os usuários logados:
 * os valores alterados são mascarados na gravação, e o IP de origem só aparece para root.
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
