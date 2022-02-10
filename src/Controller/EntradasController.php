<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Entradas Controller
 *
 * @property \App\Model\Table\EntradasTable $Entradas
 * @method \App\Model\Entity\Entrada[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class EntradasController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Categorias', 'Subcategorias', 'Users'],
        ];
        $entradas = $this->paginate($this->Entradas);

        $this->set(compact('entradas'));
    }

    /**
     * View method
     *
     * @param string|null $id Entrada id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $entrada = $this->Entradas->get($id, [
            'contain' => ['Categorias', 'Subcategorias', 'Users'],
        ]);

        $this->set(compact('entrada'));
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
        $entrada = $this->Entradas->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {

            $entrada = $this->Entradas->patchEntity($entrada, $this->request->getData());

            debug($entrada);
            if ($this->Entradas->savee($entrada)) {
                $this->Flash->success(__('The entrada has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The entrada could not be saved. Please, try again.'));
        }

        $categorias = $this->Entradas->Categorias
        ->find('all')
        ->order(['nome']);

        // $subcategorias = $this->Entradas->Categorias->Subcategorias
        // ->find('all')
        // ->order(['nome']);

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
}
