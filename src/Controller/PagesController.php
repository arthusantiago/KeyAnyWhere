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

use App\Model\Table\LogsTable;
use App\Model\Table\IpsBloqueadosTable;
use App\Model\Table\SessionsTable;
use Cake\Core\Configure;

/**
 * Static content controller
 *
 * This controller will render views from templates/Pages/
 *
 * @link https://book.cakephp.org/4/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{
    public function home()
    {
        $logs = (new LogsTable())
            ->find('countAtividadesSuspeitas')
            ->toArray();

        $ipsBloqueados = (new IpsBloqueadosTable)
            ->find('ultimosBloqueados')
            ->toArray();

        $sessoes = (new SessionsTable)
            ->find('all')
            ->select(['expires', 'created', 'user_agent'])
            ->contain(['Users' => ['fields' => ['username']]])
            ->orderByDesc('sessions.created');

        $sessoesAtivas = [];
        $timeout = Configure::read('Session.timeout');
        foreach ($sessoes as $sessao) {
            if ($sessao->user && $sessao->estaAtiva($timeout)) {
                $sessoesAtivas[] = $sessao;
            }
        }

        $this->set(compact('logs', 'ipsBloqueados', 'sessoesAtivas'));
    }
}
