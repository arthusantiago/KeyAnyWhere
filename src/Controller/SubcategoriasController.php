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
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->request->allowMethod(['post']);

        $subcategoria = $this->Subcategorias->newEmptyEntity();
        $subcategoria = $this->Subcategorias->patchEntity($subcategoria, $this->request->getData());

        if ($this->Subcategorias->save($subcategoria)) {
            $this->Flash->success(__('Salvo com sucesso'));
        }else{
            $this->Flash->error(null, ['params' => ['mensagens' => $subcategoria->getErrors()]]);    
        }
        
        return $this->redirect(['controller'=>'Categorias', 'action' => 'edit', $subcategoria->categoria_id]);
    }

    /**
     * Edit method
     *
     * @param string $id Subcategoria id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id)
    {
        $subcategoria = $this->Subcategorias->get($id, ['contain' => ['Categorias']]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $subcategoria = $this->Subcategorias->patchEntity($subcategoria, $this->request->getData());
            if ($this->Subcategorias->save($subcategoria)) {
                $this->Flash->success(__('Salvo com sucesso'));
            }else{
                $this->Flash->error(null, ['params' => ['mensagens' => $subcategoria->getErrors()]]);
            }

            return $this->redirect(['controller' => 'Categorias', 'action' => 'edit', $subcategoria->categoria_id]);
            
        }

        $this->set(compact('subcategoria'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Subcategoria id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $subcategoria = $this->Subcategorias->get($id);
        if ($this->Subcategorias->delete($subcategoria)) {
            $this->Flash->success(__('Apagado com sucesso'));
        } else {
            $this->Flash->error(null, ['params' => ['mensagens' => $subcategoria->getErrors()]]);
        }

        return $this->redirect(['controller' => 'Categorias', 'action' => 'edit', $subcategoria->categoria_id]);
    }

    /**
     * Busca no banco todas as subcategorias pertecentes a categoria informada
     *
     * @param string|null $id $categoria_id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function assincSubcategorias($categoria_id)
    {
        $this->viewBuilder()->setLayout('layout_vazio');
        $this->request->allowMethod(['post', 'get']);
        $subcategorias = $this->Subcategorias
            ->find()
            ->where(['categoria_id' => $categoria_id])
            ->order(['nome']);

        $this->set(compact('subcategorias', 'categoria_id'));
    }
}
