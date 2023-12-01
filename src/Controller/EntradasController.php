<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Validation\Validator;
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
        $this->FormProtection->setConfig('unlockedActions', ['busca', 'senhaInsegura', 'clipboard']);
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
                $this->Flash->success(__('Salvo com sucesso'));
                return $this->redirect(['action' => 'edit', $entrada->id]);
            }
            $this->Flash->error(null, ['params' => ['mensagens' => $entrada->getErrors()]]);
        }

        $categorias = $this->Entradas->Categorias
        ->find('all')
        ->order(['posicao']);

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
            ->order(['posicao']);

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
            $this->Flash->success(__('Excluído com sucesso'));
        } else {
            $this->Flash->error(null, ['params' => ['mensagens' => $entrada->getErrors()]]);
        }

        return $this->redirect($this->request->referer());
    }

    /**
     * Retorna o usuário ou senha de uma entrada.
     *
     * @param string|null $entrada_id Entrada id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function clipboard()
    {
        $this->request->allowMethod(['post']);
        $request = $this->request->getParsedBody();
        $validator = new Validator();

        $validator
            ->requirePresence('id', true, 'O ID da entrada precisa ser informado')
            ->notEmptyString('id', 'O ID não pode estar vazio')
            ->integer('id', 'O ID precisa ser um inteiro')
            ->requirePresence('type', true, 'O type precisa ser informado')
            ->notEmptyString('type', 'O type não pode estar vazio')
            ->maxLength('type', 8,'O type pode ter no maximo 8 caracteres')
            ->inList('type', ['password', 'user'], 'O valor de type não é permitido');

        $erros = $validator->validate($request);

        if ($erros) {
            return $this->response
                ->withType('application/json')
                ->withStatus(400, 'Dados invalidos enviados ao servidor')
                ->withStringBody(json_encode($erros));
        }

        $entrada = $this->Entradas->get($request['id']);

        if ($request['type'] == 'password') {
            $response['data'] = $entrada->passwordDescrip();
        } else if ($request['type'] == 'user'){
            $response['data'] = $entrada->usernameDescrip();
        }

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($response));
    }

    /**
     * Busca por entrada.
     *
     */
    public function busca()
    {
        $this->request->allowMethod(['post']);
        $pesquisa = $this->request->getParsedBody();
        $pesquisa['stringBusca'] = strtolower($pesquisa['stringBusca']);

        $query = $this->Entradas
            ->find()
            ->select(['id','titulo']);

        $resultado = [];
        foreach($query as $entrada){
            if (str_contains(strtolower($entrada->tituloDescrip()), $pesquisa['stringBusca'])) {
                $resultado[] = $entrada;
            }

            if (count($resultado) > 9) {
                break;
            }
        }

        $this->viewBuilder()->setLayout('layout_vazio');
        $this->set(compact('resultado'));
    }

    /**
     * Recebe uma senha e verifica se ela está no catalogo de senhas inseguras.
     * Espera uma request com o header: 'Content-Type': 'application/json'
     * Espera uma request com JSON no formato: {"password":"senha-para-verificacao"}
     *
     * @access	public
     * @return \Cake\Http\Response
     */
    public function senhaInsegura()
    {
        $this->request->allowMethod(['post']);
        $request = $this->request->getParsedBody();

        $password = $this->fetchTable('InsecurePasswords')
            ->find()
            ->where(['password' => strtolower($request['password'])])
            ->limit(5)
            ->first();

        $resultado['localizado'] = $password ? true : false;

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($resultado));
    }
}
