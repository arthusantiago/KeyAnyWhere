<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Subcategorias Controller
 *
 * @property \App\Model\Table\SubcategoriasTable $Subcategorias
 * @method \App\Model\Entity\Subcategoria[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SubcategoriasController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Categorias'],
        ];
        $subcategorias = $this->paginate($this->Subcategorias);

        $this->set(compact('subcategorias'));
    }

    /**
     * View method
     *
     * @param string|null $id Subcategoria id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $subcategoria = $this->Subcategorias->get($id, [
            'contain' => ['Categorias', 'Entradas'],
        ]);

        $this->set(compact('subcategoria'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $subcategoria = $this->Subcategorias->newEmptyEntity();
        if ($this->request->is('post')) {
            $subcategoria = $this->Subcategorias->patchEntity($subcategoria, $this->request->getData());
            if ($this->Subcategorias->save($subcategoria)) {
                $this->Flash->success(__('The subcategoria has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The subcategoria could not be saved. Please, try again.'));
        }
        $categorias = $this->Subcategorias->Categorias->find('list', ['limit' => 200])->all();
        $this->set(compact('subcategoria', 'categorias'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Subcategoria id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $subcategoria = $this->Subcategorias->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $subcategoria = $this->Subcategorias->patchEntity($subcategoria, $this->request->getData());
            if ($this->Subcategorias->save($subcategoria)) {
                $this->Flash->success(__('The subcategoria has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The subcategoria could not be saved. Please, try again.'));
        }
        $categorias = $this->Subcategorias->Categorias->find('list', ['limit' => 200])->all();
        $this->set(compact('subcategoria', 'categorias'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Subcategoria id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $subcategoria = $this->Subcategorias->get($id);
        if ($this->Subcategorias->delete($subcategoria)) {
            $this->Flash->success(__('The subcategoria has been deleted.'));
        } else {
            $this->Flash->error(__('The subcategoria could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
