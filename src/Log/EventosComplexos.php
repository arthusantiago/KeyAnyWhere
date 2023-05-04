<?php

namespace App\Log;

use App\Log\Eventos;
use App\Log\Gerenciador\GerenciamentoLogs;
use Cake\Core\Exception\CakeException;
use App\Model\Table\LogsTable;
use Cake\I18n\FrozenTime;

/**
 * Classe que gerencia os eventos que requerem uma lógica mais elaborada em seu disparo.
 *
 * @global
 */
class EventosComplexos
{
    private $tableLogs;
    private $frozenTime;

    function __construct() {
        $this->tableLogs = new LogsTable();
        $this->frozenTime = FrozenTime::now();
    }

    /**
     * Recebe a notificação de que um novo log foi salvo.
     *
     * @access	public
     * @param	array $eventoOrigemLog Evento que foi salvo como log.
     * @return	void
     */
    public function novoLogSalvo(array $eventoOrigemLog): void
    {
        //eventos que dependem deste para serem disparados
        $eventos = $this->ehUmEventoGatilho($eventoOrigemLog['evento']);

        if ($eventos) {
            $this->executaMetodoEspecifico($eventos, $eventoOrigemLog);
        }
    }

    /**
     * Verifica se o evento que foi salvo é aguardado por outros eventos.
     *
     * @access	private
     * @param	string	$idEvento
     * @return	null|array
     */
    private function ehUmEventoGatilho(string $idEvento): ?array
    {
        $eventosQueAguardam = null;
        $catalogoEventos = Eventos::getEventos();
        foreach ($catalogoEventos as $identificador => $propriedades) {
            if (
                isset($propriedades['evento_gatilho'])
                && in_array($idEvento, $propriedades['evento_gatilho'])
            ) {
                $eventosQueAguardam[] = $identificador;
            }
        }

        return $eventosQueAguardam;
    }

    /**
     * Executa o método que contém a lógica do evento que aguardava o outro ser disparado.
     *
     * @access	private
     * @param	array	$eventos
     * @param	array	$eventoOrigemLog
     * @return	void
     */
    private function executaMetodoEspecifico(array $eventos, array $eventoOrigemLog): void
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
     * Evento descrito no catálogo src\Log\Eventos::$catalogoEventos
     *
     * @access	private
     * @param	array	$eventoOrigemLog
     * @return	void
     */
    private function C1_3(array $eventoOrigemLog): void
    {
        $C1_1 = $this->tableLogs
            ->find()
            ->where([
                "evento LIKE 'C1-1'" ,
                'created >= ' => $this->frozenTime->i18nFormat('yyyy-MM-dd 00:00:00'),
                'created <= ' => $this->frozenTime->i18nFormat('yyyy-MM-dd 23:59:59'),
            ])
            ->toArray();

        $C1_2 = $this->tableLogs
            ->find()
            ->where([
                "evento LIKE 'C1-2'",
                'created >= ' => $this->frozenTime->i18nFormat('yyyy-MM-dd 00:00:00'),
                'created <= ' => $this->frozenTime->i18nFormat('yyyy-MM-dd 23:59:59'),
            ])
            ->toArray();

        if (count($C1_1) >= 3 || count($C1_2) >= 3) {
            GerenciamentoLogs::novoEvento(['evento' => 'C1-3', 'usuario' => $eventoOrigemLog['usuario']]);
        }
    }

    /**
     * Evento descrito no catálogo src\Log\Eventos::$catalogoEventos
     *
     * @access	private
     * @param	array	$eventoOrigemLog
     * @return	void
     */
    private function C1_4(array $eventoOrigemLog)
    {
        $C1_3 = $this->tableLogs
            ->find()
            ->where([
                "evento LIKE 'C1-3'",
                'created >= ' => $this->frozenTime->i18nFormat('yyyy-MM-dd 00:00:00'),
                'created <= ' => $this->frozenTime->i18nFormat('yyyy-MM-dd 23:59:59'),
            ])
            ->toArray();

        if (count($C1_3) >= 2) {
            GerenciamentoLogs::novoEvento(['evento' => 'C1-4', 'usuario' => $eventoOrigemLog['usuario']]);
        }
    }
}
