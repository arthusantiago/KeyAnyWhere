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
            ['order' => ['created' => 'desc']]
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
    public function view($id = null)
    {
        $log = $this->Logs->get($id);

        $this->viewBuilder()->setLayout('administrativo');
        $this->set(compact('log'));
    }

    public function analisado($id)
    {
        if ($this->request->is(['post']))
        {
            $log = $this->Logs->get($id);
            $log = $this->Logs->patchEntity($log, $this->request->getData());
            if ($this->Logs->save($log)) {
                $this->Flash->success(__('Salvo com sucesso'));
            }else{
                $this->Flash->error(null, ['params' => ['mensagens' => $log->getErrors()]]);
            }
        }

        return $this->redirect($this->referer());
    }
}
