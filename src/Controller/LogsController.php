<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Logs Controller
 *
 * @property \App\Model\Table\LogsTable $Logs
 * @method \App\Model\Entity\Log[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class LogsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $logs = $this->paginate(
            $this->Logs,
            [
                'order' => ['created' => 'desc'],
                'limit' => 15,
            ],
        );

        $this->viewBuilder()->setLayout('administrativo');
        $this->set(compact('logs'));
    }

    /**
     * View method
     *
     * @param string|null $id Log id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $log = $this->Logs->get($id);

        $this->viewBuilder()->setLayout('administrativo');
        $this->set(compact('log'));
    }

    /**
     * Alterna o status 'analisado' do log informado.
     *
     * @param string|int $id Log id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     */
    public function analisado(string|int $id)
    {
        $this->request->allowMethod(['get']);

        $log = $this->Logs->get($id);
        $log->analisado = !$log->analisado;

        if ($this->Logs->save($log)) {
            $this->Flash->success(__('Salvo com sucesso'));
        } else {
            $this->Flash->error('', ['params' => ['mensagens' => $log->getErrors()]]);
        }

        return $this->redirect(['action' => 'index']);
    }
}
