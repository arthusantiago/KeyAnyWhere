<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

/**
 * IpsBloqueados Controller
 *
 * @property \App\Model\Table\IpsBloqueadosTable $IpsBloqueados
 * @method \App\Model\Entity\IpsBloqueado[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class IpsBloqueadosController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->FormProtection->setConfig('unlockedActions', ['delete']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $ipsBloqueados = $this->paginate(
            $this->IpsBloqueados,
            [
                'order' => ['created' => 'desc'],
                'limit' => 15
            ]
        );

        $this->viewBuilder()->setLayout('administrativo');
        $this->set(compact('ipsBloqueados'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $ipsBloqueado = $this->IpsBloqueados->newEmptyEntity();
        if ($this->request->is('post')) {
            $ipsBloqueado = $this->IpsBloqueados->patchEntity($ipsBloqueado, $this->request->getData());
            if ($this->IpsBloqueados->save($ipsBloqueado)) {
                $this->Flash->success(__('Salvo com sucesso!'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(null, ['params' => ['mensagens' => $ipsBloqueado->getErrors()]]);
        }

        $this->viewBuilder()->setLayout('administrativo');
        $this->set(compact('ipsBloqueado'));
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
        $ipsBloqueado = $this->IpsBloqueados->get($id);
        if ($this->IpsBloqueados->delete($ipsBloqueado)) {
            $this->Flash->success(__('Excluído com sucesso'));
        } else {
            $this->Flash->error(null, ['params' => ['mensagens' => $ipsBloqueado->getErrors()]]);
        }

        return $this->redirect(['action' => 'index']);
    }
}
