<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Categorias Controller
 *
 * @property \App\Model\Table\CategoriasTable $Categorias
 * @method \App\Model\Entity\Categoria[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CategoriasController extends AppController
{
    /**
     * A listagem das entradas da categoria informada
     *
     * @param string $id Categoria id
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function listagemEntradas(string $id)
    {
        $entradas = $this->Categorias->Entradas
        ->find('all')
        ->where(['categoria_id' => $id])
        ->order(['titulo']);

        $this->set(compact('entradas'));
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Categorias
        ->find('all')
        ->order(['nome']);

        $query = $this->paginate($query);

        $this->set(compact('query'));
    }

    /**
     * View method
     *
     * @param string|null $id Categoria id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $categoria = $this->Categorias->get($id, [ 'contain' => ['Entradas']]);
        $this->set(compact('categoria'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $categoria = $this->Categorias->newEmptyEntity();
        if ($this->request->is('post')) {
            $categoria = $this->Categorias->patchEntity($categoria, $this->request->getData());
            if ($this->Categorias->save($categoria)) {
                $this->Flash->success(__('Salvo com sucesso'));
                return $this->redirect(['action' => 'edit', $categoria->id]);
            }
            $this->Flash->error(__('Erro ao salvar'));
        }
        $this->set(compact('categoria'));
    }

    /**
     * Edit method
     *
     * @param string $id Categoria id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id)
    {
        $categoria = $this->Categorias->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $categoria = $this->Categorias->patchEntity($categoria, $this->request->getData());
            if ($this->Categorias->save($categoria)) {
                $this->Flash->success(__('Salvo com sucesso'));
            }else{
                $this->Flash->error(null, ['params' => ['mensagens' => $categoria->getErrors()]]);
            }

            return $this->redirect(['action' => 'edit', $categoria->id]);
        }

        $this->set(compact('categoria'));
    }

    /**
     * Delete method
     * O delete acontece em cascata: categoria > entrada
     *
     * @param string|null $id Categoria id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $categoria = $this->Categorias->get($id);
        if ($this->Categorias->delete($categoria)) {
            $this->Flash->success(__('Excluído com sucesso'));
        } else {
            $this->Flash->error(__('Erro ao excluir'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
