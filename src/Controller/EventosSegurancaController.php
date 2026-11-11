<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Consulta somente leitura dos eventos de segurança (tabela `eventos_seguranca`, gravada pelo
 * App\Log\Engine\EventosSegurancaLog). Todos os usuários veem os eventos de todos; só o root vê o
 * IP de origem (dado pessoal).
 *
 * @property \App\Model\Table\EventosSegurancaTable $EventosSeguranca
 */
class EventosSegurancaController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('administrativo');
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $eventos = $this->paginate(
            $this->EventosSeguranca->find('recentes'),
            ['limit' => 30],
        );

        $this->set(compact('eventos'));
    }
}
