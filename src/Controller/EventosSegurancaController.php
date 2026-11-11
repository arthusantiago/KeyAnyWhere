<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use Cake\Http\Exception\ForbiddenException;

/**
 * Consulta somente leitura dos eventos de segurança (tabela `eventos_seguranca`, gravada pelo
 * App\Log\Engine\EventosSegurancaLog). Cada usuário vê os eventos da própria conta; o root vê todos e
 * o IP de origem.
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
        $usuario = $this->Authentication->getResult()?->getData();
        if (!$usuario instanceof User) {
            throw new ForbiddenException();
        }

        $eventos = $this->paginate(
            $this->EventosSeguranca->find('visiveisPara', usuario: $usuario),
            ['limit' => 30],
        );

        $this->set(compact('eventos'));
    }
}
