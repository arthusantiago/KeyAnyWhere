<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Model\Table\IpsBloqueadosTable;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('FormProtection');

        $userLogado = null;
        if ($this->getRequest()->getAttribute('authentication')) {
            $userLogado = $this->Authentication->getResult()->getData();
        }
        $sessionTimeout = Configure::read('Session.timeout');
        $csrfToken = $this->request->getAttribute('csrfToken');

        $this->set(compact('userLogado', 'sessionTimeout', 'csrfToken'));
    }

    /**
     * beforeFilter callback.
     *
     * Se o IP estiver bloqueado, a página de bloqueio é colocada como resultado do evento
     * e a propagação é interrompida: Controller::startupProcess() devolve essa resposta
     * direto, sem executar a action. Controllers filhos que sobrescrevem este método e têm
     * lógica que não deve rodar para um IP bloqueado (ex.: redirecionamentos, eventos de
     * auditoria) devem checar `$event->isStopped()` logo após `parent::beforeFilter($event)`.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return void
     */
    public function beforeFilter(EventInterface $event)
    {
        if ($this->ipEstaBloqueado()) {
            $this->viewBuilder()->setLayout('layout_vazio');
            $event->setResult($this->render('/IpsBloqueados/ip_bloqueado'));
            $event->stopPropagation();
        }
    }

    /**
     * Verifica se o IP de quem está acessar, foi bloqueado
     *
     * @access private
     * @return bool
     */
    private function ipEstaBloqueado(): bool
    {
        $ipBloqueado = (new IpsBloqueadosTable())
            ->find()
            ->where(['ip' => $this->request->clientIp()])
            ->limit(1)
            ->toArray();

        return empty($ipBloqueado) ? false : true;
    }
}
