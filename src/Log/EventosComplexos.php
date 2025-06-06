<?php
declare(strict_types=1);

namespace App\Log;

use App\Model\Table\IpsBloqueadosTable;
use App\Model\Table\LogsTable;
use Cake\Core\Exception\CakeException;
use Cake\I18n\DateTime;
use Cake\Log\Log;

/**
 * Classe que gerencia os eventos que requerem uma lógica mais elaborada em seu disparo.
 *
 * @global
 */
class EventosComplexos
{
    private $tableLogs;
    private $frozenTime;

    function __construct()
    {
        $this->tableLogs = new LogsTable();
        $this->frozenTime = DateTime::now();
    }

    /**
     * Recebe a notificação de que um novo log foi salvo.
     *
     * @access public
     * @param \App\Log\Evento $eventoOrigemLog Evento que foi salvo como log.
     * @return void
     */
    public function novoLogSalvo(Evento $eventoOrigemLog): void
    {
        //eventos que dependem deste para serem disparados
        $eventos = $this->ehUmEventoGatilho($eventoOrigemLog->getId());

        if ($eventos) {
            $this->executaMetodoEspecifico($eventos, $eventoOrigemLog);
        }
    }

    /**
     * Verifica se o evento que foi salvo é aguardado por outros eventos.
     *
     * @access private
     * @param string  $idEvento
     * @return array|null
     */
    private function ehUmEventoGatilho(string $idEvento): ?array
    {
        $eventosQueAguardam = null;
        $catalogoEventos = GerenciadorEventos::getEventos();
        foreach ($catalogoEventos as $identificador => $evento) {
            if ($evento->aguardaPeloEvento($idEvento)) {
                $eventosQueAguardam[] = $identificador;
            }
        }

        return $eventosQueAguardam;
    }

    /**
     * Executa o método que contém a lógica do evento que aguardava o outro ser disparado.
     *
     * @access private
     * @param array   $eventos
     * @param \App\Log\Evento  $eventoOrigemLog
     * @return void
     */
    private function executaMetodoEspecifico(array $eventos, Evento $eventoOrigemLog): void
    {
        foreach ($eventos as $idEvento) {
            $nomeMetodo = str_replace('-', '_', $idEvento);
            if (method_exists('App\Log\EventosComplexos', $nomeMetodo)) {
                $this->$nomeMetodo($eventoOrigemLog);
            } else {
                throw new CakeException('O método App\Log\EventosComplexos::' . $nomeMetodo . '() precisa ser implementado');
            }
        }
    }

    /**
     * Evento descrito no catálogo src\Log\GerenciadorEventos::$catalogoEventos
     *
     * @access private
     * @param \App\Log\Evento  $eventoOrigemLog
     * @return void
     */
    private function C1_3(Evento $eventoOrigemLog): void
    {
        $C1_1 = $this->tableLogs
            ->find()
            ->where([
                "evento LIKE 'C1-1'" ,
                'created >= ' => $this->frozenTime->i18nFormat('yyyy-MM-dd 00:00:00'),
                'created <= ' => $this->frozenTime->i18nFormat('yyyy-MM-dd 23:59:59'),
            ])
            ->limit(5)
            ->toArray();

        $C1_2 = $this->tableLogs
            ->find()
            ->where([
                "evento LIKE 'C1-2'",
                'created >= ' => $this->frozenTime->i18nFormat('yyyy-MM-dd 00:00:00'),
                'created <= ' => $this->frozenTime->i18nFormat('yyyy-MM-dd 23:59:59'),
            ])
            ->limit(5)
            ->toArray();

        if (count($C1_1) >= 3 || count($C1_2) >= 3) {
            $ipTable = new IpsBloqueadosTable();
            $novoIp = $ipTable->newEmptyEntity();
            $novoIp->ip = $eventoOrigemLog->getIpOrigem();

            if ($ipTable->save($novoIp) == false) {
                $mensagensErro = [];
                $erros = $novoIp->getErrors();
                array_walk_recursive(
                    $erros,
                    function ($msg, $tipoErro) use (&$mensagensErro): void {
                        $mensagensErro[] = $msg;
                    },
                );

                Log::error('Erro ao salvar no DB o bloqueio do IP ' . $novoIp->ip . ' | Erros: ' . implode(',', $mensagensErro));
            }

            GerenciadorEventos::notificarEvento([
                'evento' => 'C1-3',
                'request' => $eventoOrigemLog->getRequest(),
                'usuario' => [
                    'dados' => [
                        'email'  => $eventoOrigemLog->getRequest()->getData('email'),
                    ],
                    'texto' => 'Credenciais utilizadas para logar: ',
                ],
            ]);
        }
    }
}
