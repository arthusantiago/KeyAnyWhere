<?php
declare(strict_types=1);

namespace App\Controller;

use App\I18n\Regional;
use App\Log\Seguranca;
use App\Model\Entity\Configuracao;
use App\Model\Entity\User;
use Cake\Event\EventInterface;

/**
 * Configurações gerais da instalação (somente root): fuso horário e idioma. Os valores ficam na tabela `configuracoes` e são aplicados no
 * config/bootstrap.php (`App\I18n\Regional`).
 *
 * @property \App\Model\Table\ConfiguracoesTable $Configuracoes
 */
class ConfiguracoesController extends AppController
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
     * Todas as actions são somente root.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $user = $this->Authentication->getResult()?->getData();
        // Sem usuário autenticado, o AuthenticationComponent barra a requisição logo depois.
        if ($user !== null && !($user instanceof User && $user->root)) {
            $this->Flash->error('Você não tem permissão');
            Seguranca::registrar(
                'warning',
                Seguranca::ACESSO_SOMENTE_ROOT,
                'O usuário tentou acessar um recurso que somente o root tem permissão',
            );

            // CakePHP 5.2+: o redirect vai no resultado do evento, não no retorno do callback
            $event->setResult($this->redirect(['controller' => 'Pages', 'action' => 'home']));
            $event->stopPropagation();
        }
    }

    /**
     * Tela "Configurações gerais": fuso horário e idioma.
     *
     * @return \Cake\Http\Response|null|void
     */
    public function geral()
    {
        $this->request->allowMethod(['get', 'post', 'put']);
        $preferencias = $this->Configuracoes->preferenciasRegionais() + [
            'fusoHorario' => date_default_timezone_get(),
            'idioma' => Regional::IDIOMA_PADRAO,
        ];

        if ($this->request->is(['post', 'put'])) {
            $dados = [
                'fusoHorario' => (string)$this->request->getData('fusoHorario'),
                'idioma' => (string)$this->request->getData('idioma'),
            ];
            $erros = $this->Configuracoes->getValidator('regional')->validate($dados);

            if (!$erros) {
                $tabela = $this->Configuracoes;
                $salvou = $tabela->getConnection()->transactional(function () use ($tabela, $dados): bool {
                    $fuso = $tabela->salvarValorConfig(Configuracao::REGIONAL_FUSO_HORARIO, $dados['fusoHorario']);
                    $idioma = $tabela->salvarValorConfig(Configuracao::REGIONAL_IDIOMA, $dados['idioma']);

                    return $fuso !== false && $idioma !== false;
                });

                if ($salvou) {
                    $this->Flash->success('');

                    // Redireciona para a próxima requisição já carregar o fuso e o idioma novos.
                    return $this->redirect(['action' => 'geral']);
                }
            }

            $this->Flash->error('', ['params' => ['mensagens' => $erros]]);
            $preferencias = $dados;
        }

        $this->set([
            'preferencias' => $preferencias,
            'idiomas' => Regional::IDIOMAS,
            'fusosHorarios' => Regional::fusosHorarios(),
        ]);
    }
}
