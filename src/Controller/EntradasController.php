<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
/**
 * Entradas Controller
 *
 * @property \App\Model\Table\EntradasTable $Entradas
 * @method \App\Model\Entity\Entrada[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class EntradasController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        // desabilitando o cache por segurança.
        $this->response = $this->response->withDisabledCache();
        $this->FormProtection->setConfig('unlockedActions', ['busca']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $entrada = $this->Entradas->newEmptyEntity();
        if ($this->request->is('post')) {
            $entrada = $this->Entradas->patchEntity($entrada, $this->request->getData());
            //setar aqui o ID do usuário logado
            $entrada->user_id = $this->request->getAttribute('identity')->getIdentifier();

            if ($this->Entradas->save($entrada)) {
                $this->Flash->success(__('The entrada has been saved.'));

                return $this->redirect(['action' => 'edit', $entrada->id]);
            }
            $this->Flash->error(__('The entrada could not be saved. Please, try again.'));
        }
        $categorias = $this->Entradas->Categorias
        ->find('all')
        ->order(['nome']);

        $this->set(compact('entrada', 'categorias'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Entrada id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id)
    {
        $entrada = $this->Entradas->get($id, ['contain' => ['Categorias']]);
        if ($this->request->is(['patch', 'post', 'put']))
        {
            $entrada = $this->Entradas->patchEntity($entrada, $this->request->getData());
            if ($this->Entradas->save($entrada)) {
                $this->Flash->success(__('Salvo com sucesso'));
            }else{
                $this->Flash->error(null, ['params' => ['mensagens' => $entrada->getErrors()]]);
            }
            return $this->redirect(['action' => 'edit', $entrada->id]);
        }

        $categorias = $this->Entradas->Categorias
            ->find('all')
            ->order(['nome']);

        $this->set(compact('entrada', 'categorias'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Entrada id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $entrada = $this->Entradas->get($id);
        if ($this->Entradas->delete($entrada)) {
            $this->Flash->success(__('The entrada has been deleted.'));
        } else {
            $this->Flash->error(__('The entrada could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Busca a entrada para retornar a senha dela
     *
     * @param string|null $entrada_id Entrada id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function clipboardPass($entrada_id)
    {
        $entrada = $this->Entradas->get($entrada_id);
        $this->viewBuilder()->setLayout('layout_vazio');
        $this->set(compact('entrada'));
    }

    /**
     * Busca a entrada para retornar o usuário dela
     *
     * @param string|null $entrada_id Entrada id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function clipboardUser($entrada_id)
    {
        $entrada = $this->Entradas->get($entrada_id);
        $this->viewBuilder()->setLayout('layout_vazio');
        $this->set(compact('entrada'));
    }

    /**
     * Busca por entrada.
     *
     */
    public function busca()
    {
        $this->request->allowMethod(['post']);
        $pesquisa = $this->request->getParsedBody();
        $resultado = $this->Entradas
            ->find()
            ->where(['lower(titulo) LIKE' => strtolower('%' . $pesquisa['stringBusca'] . '%')])
            ->limit(10);
        $this->viewBuilder()->setLayout('layout_vazio');
        $this->set(compact('resultado'));
    }
}
