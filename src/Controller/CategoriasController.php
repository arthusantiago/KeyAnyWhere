<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
/**
 * Categorias Controller
 *
 * @property \App\Model\Table\CategoriasTable $Categorias
 * @method \App\Model\Entity\Categoria[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CategoriasController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        // desabilitando o cache por segurança.
        $this->response = $this->response->withDisabledCache();
    }

    /**
     * A listagem das entradas da categoria informada
     *
     * @param string $categoria_id Categoria id
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function listagemEntradas(string $categoria_id)
    {
        $entradas = $this->Categorias->Entradas
            ->find('all')
            ->where(['categoria_id' => $categoria_id])
            ->order(['titulo']);

        $entradas = $this->paginate($entradas, ['limit' => 15]);

        $this->set(compact('entradas','categoria_id'));
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
        ->order(['posicao']);

        $query = $this->paginate($query, ['limit' => 15]);

        $this->set(compact('query'));
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
                $this->Categorias->reordenar();
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
                $this->Categorias->reordenar();
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
            $this->Categorias->reordenar();
        } else {
            $this->Flash->error(null, ['params' => ['mensagens' => $categoria->getErrors()]]);
        }

        return $this->redirect(['action' => 'index']);
    }
}
