<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\VerificadorSenhaInseguraInterface;
use Cake\Event\EventInterface;
use Cake\Routing\Router;
use function Cake\I18n\__;

/**
 * Entradas Controller
 *
 * @property \App\Model\Table\EntradasTable $Entradas
 * @method \App\Model\Entity\Entrada[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class EntradasController extends AppController
{
    private string $contentType = 'application/json; charset=UTF-8';
    private string $msgHttpCode400 = 'Dados invalidos enviados ao servidor';

    /**
     * beforeFilter callback.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        // desabilitando o cache por segurança.
        $this->response = $this->response->withDisabledCache();
        $this->FormProtection->setConfig('unlockedActions', ['busca', 'senhaInsegura', 'clipboard', 'delete']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $entrada = $this->Entradas->newEmptyEntity();
        // Categoria já escolhida no formulário (botão de nova entrada da listagem da categoria).
        $categoria_selecionada = (int)$this->request->getQuery('categoria', 0);
        if ($this->request->is('post')) {
            $entrada = $this->Entradas->patchEntity($entrada, $this->request->getData());

            if ($this->Entradas->save($entrada)) {
                $this->Flash->success(__('Salvo com sucesso'));
                $this->Entradas->reordenar($entrada->categoria_id);

                return $this->redirect(['action' => 'edit', $entrada->id]);
            }
            $this->Flash->error('', ['params' => ['mensagens' => $entrada->getErrors()]]);
        }

        $categorias = $this->Entradas->Categorias
        ->find('all')
        ->orderBy(['posicao']);

        $this->set(compact('entrada', 'categorias', 'categoria_selecionada'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Entrada id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id)
    {
        $entrada = $this->Entradas->get($id, contain: ['Categorias']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $entrada = $this->Entradas->patchEntity($entrada, $this->request->getData());
            if ($this->Entradas->save($entrada)) {
                $this->Flash->success(__('Salvo com sucesso'));
                $this->Entradas->reordenar($entrada->categoria_id);
            } else {
                $this->Flash->error('', ['params' => ['mensagens' => $entrada->getErrors()]]);
            }

            return $this->redirect(['action' => 'edit', $entrada->id]);
        }

        $categorias = $this->Entradas->Categorias
            ->find('all')
            ->orderBy(['posicao']);

        $this->set(compact('entrada', 'categorias'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Entrada id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete()
    {
        $this->request->allowMethod(['post', 'delete']);
        $entrada = $this->Entradas->get($this->request->getData('id'));
        if ($this->Entradas->delete($entrada)) {
            $this->Flash->success(__('Excluído com sucesso'));
        } else {
            $this->Flash->error('', ['params' => ['mensagens' => $entrada->getErrors()]]);
        }

        return $this->redirect(['controller' => 'categorias', 'action' => 'listagemEntradas', $entrada->categoria_id]);
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
        $erros = $this->Entradas
            ->getValidator('clipboard')
            ->validate($request, false);

        if ($erros) {
            return $this->response
                ->withType($this->contentType)
                ->withStatus(400, __($this->msgHttpCode400))
                ->withStringBody(json_encode($erros));
        }

        $entrada = $this->Entradas->get($request['id']);

        if ($request['type'] == 'password') {
            $response['data'] = $entrada->passwordDescrip();
        } elseif ($request['type'] == 'user') {
            $response['data'] = $entrada->usernameDescrip();
        }

        return $this->response
            ->withType($this->contentType)
            ->withStringBody(json_encode($response));
    }

    /**
     * Busca por entrada.
     *
     * Retorna JSON (não HTML) propositalmente: o cliente monta o resultado no DOM
     * via createElement/textContent (nunca innerHTML) para não expor um sink de
     * DOM XSS client-side, mesmo o título já vindo escapado nesse ponto.
     *
     * @return \Cake\Http\Response
     */
    public function busca()
    {
        $this->request->allowMethod(['post']);
        $request = $this->request->getParsedBody();
        $erros = $this->Entradas
            ->getValidator('busca')
            ->validate($request, false);

        if ($erros) {
            return $this->response
                ->withType($this->contentType)
                ->withStatus(400, __($this->msgHttpCode400))
                ->withStringBody(json_encode($erros));
        }

        $request['stringBusca'] = strtolower($request['stringBusca']);

        $query = $this->Entradas
            ->find()
            ->select(['id','titulo']);

        $resultado = [];
        foreach ($query as $entrada) {
            $texto = $entrada->tituloDescrip();

            if (str_contains(strtolower($texto), $request['stringBusca'])) {
                if (strlen($texto) > 30) {
                    $texto = substr($texto, 0, 30) . ' (...)';
                }

                $resultado[] = [
                    'titulo' => $texto,
                    'url' => Router::url(
                        ['controller' => 'Entradas', 'action' => 'edit', $entrada->id],
                        true,
                    ),
                ];
            }

            if (count($resultado) > 9) {
                break;
            }
        }

        return $this->response
            ->withType($this->contentType)
            ->withStringBody(json_encode($resultado));
    }

    /**
     * Recebe uma senha e verifica se ela está no catalogo de senhas inseguras.
     * Espera uma request com o header: 'Content-Type': 'application/json'
     * Espera uma request com JSON no formato: {"password":"senha-para-verificacao"}
     *
     * @access public
     * @param \App\Service\VerificadorSenhaInseguraInterface $verificador Injetado pelo container.
     * @return \Cake\Http\Response
     */
    public function senhaInsegura(VerificadorSenhaInseguraInterface $verificador)
    {
        $this->request->allowMethod(['post']);
        $request = $this->request->getParsedBody();
        $erros = $this->Entradas
            ->getValidator('senhaInsegura')
            ->validate($request, false);

        if ($erros) {
            return $this->response
                ->withType($this->contentType)
                ->withStatus(400, __($this->msgHttpCode400))
                ->withStringBody(json_encode($erros));
        }

        $resultado['localizado'] = $verificador->estaComprometida((string)$request['password']);

        return $this->response
            ->withType($this->contentType)
            ->withStringBody(json_encode($resultado));
    }
}
