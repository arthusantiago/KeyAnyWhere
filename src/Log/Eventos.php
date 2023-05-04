<?php

namespace App\Log;

use Cake\Core\Exception\CakeException;

/**
 * Um evento é uma situação/acontecimento que já foi mapeada e definida suas propriedades.
 * Esse evento envolve algo que o sistema precisa monitorar. Quando o evento acontece, ele deve ser
 * notificado e transformado em um registro de log.
 *
 * Abaixo está descrito o fluxo criação de um evento até transformá-lo em um registro de log:
 *
 * 1. Acontece um evento. Quem o identifica também fica responsável por notificá-lo.
 *    Quem notifica o evento também deve informar todos os dados necessários.
 * 2. A classe /kaw/src/Log/GerenciamentoLogs.php fica responsável receber e transformar o evento em um log.
 *
 * Para a classificar a severidade do log, é utilizado o conceito de progressão no nÍvel do Syslog.
 * @see https://en.wikipedia.org/wiki/Syslog#Severity_level
 * @global
 */
class Eventos
{
    /**
     * Listagem de todos os eventos catalogados.
     * O evento deve perteNcer a uma categoria, que é representada pelo 'C?' do identificador do evento.
     * As categorias agrupam eventos semelhantes.
     *
     * Estrutura de um evento:
     * 'C1-1' => [
     *      'nivel_severidade_string' => 'notice', // Seguir o padrão do Syslog.
     *      'mensagem' => 'Durante o login o usuario errou o user ou password', // Texto descrevendo o problema. Esse é a menssagem do log.
     *      'evento_gatilho' => ['C1-1', 'C1-2'] // Opcional: Diz se o evento será disparado a partir do registro de outros evento. Ler classe /kaw/src/Log/EventosComplexos.php
     *  ]
     *
     * @var array $catalogoEventos
     */
    private static $catalogoEventos = [
        /* LOGIN
         *
         * Eventos que acontecerem antes, durante e depois do login no sistema.
         */
        'C1-1' => [
            'nivel_severidade_string' => 'notice',
            'mensagem' => 'Durante o login o usuario errou o user ou password'
        ],
        'C1-2' => [
            'nivel_severidade_string' => 'notice',
            'mensagem' => 'Durante o login o usuario errou o 2FA'
        ],
        'C1-3' => [
            'nivel_severidade_string' => 'warning',
            'mensagem' => 'Durante o login, o usuário erro mais de 3 vezes o user, password ou 2FA.',
            'evento_gatilho' => ['C1-1', 'C1-2']
        ],
        'C1-4' => [
            'nivel_severidade_string' => 'alert',
            'mensagem' => 'Durante o login, o usuário excedeu duas vezes a quantidade máxima de tentativas erradas.',
            'evento_gatilho' => ['C1-3']
        ],
        /* ACESSO NÃO AUTORIZADO
         *
         * Descreve as tentativas de acesso a algum recurso ao qual o usuário não tem permissão.
         */
        'C2-1' => [
            'nivel_severidade_string' => 'warning',
            'mensagem' => 'O usuário tentou acessar um recurso que somente o root tem permissão.'
        ],
    ];

    /**
     * Contem as propriedades que são comuns entre todos os eventos.
     *
     * @var	array $propComunEventos
     */
    private static $propComunEventos = [
        'evento' => null,
        'nivel_severidade' => null,
        'recurso' => null,
        'ip_origem' => null,
        'usuario' => null,
    ];

    /**
     * Retorna o evento desejado
     *
     * @access	public static
     * @param	string	$idEvento
     * @return	array
     */
    public static function getEvento(string $idEvento): array
    {
        if (self::eventoCatalogado($idEvento) == false) {
            throw new CakeException('O evento ' . $idEvento . ' não foi catalogado. Cadastre-o em App\Log\Eventos');
        }
        return self::atribuiPropriedadesComuns(strtoupper($idEvento));
    }

    /**
     * Retorna todos os eventos do catalogo
     *
     * @access	public static
     * @return	array
     */
    public static function getEventos(): array
    {
        return self::atribuiPropriedadesComuns();
    }

    /**
     * Verifica se o evento informado se encontra no catalogo de eventos.
     *
     * @access	public static
     * @param	string	$idEvento
     * @return	bool
     */
    public static function eventoCatalogado(string $idEvento): bool
    {
        return array_key_exists(strtoupper($idEvento), self::$catalogoEventos);
    }

    /**
     * Atribui na estrutura do evento as propriedades que são comuns entre todos os evento.
     *
     * @access	private static
     * @param	string	$idEvento
     * @return	array
     */
    private static function atribuiPropriedadesComuns(string $idEvento = null): array
    {
        $eventos = [];

        if (empty($idEvento)) { // atribuindo a todos os eventos
            foreach (self::$catalogoEventos as $identificador => $propriedades) {
                $eventos[$identificador] = $propriedades;
                foreach (self::$propComunEventos as $propriedade => $valor) {
                    $eventos[$identificador][$propriedade] = $valor;
                }
                $eventos[$identificador]['evento'] = $identificador;
            }
        } else { // trabalhando somente com um evento
            $eventos =  self::$catalogoEventos[$idEvento];
            foreach (self::$propComunEventos as $propriedade => $valor) {
                $eventos[$propriedade] = $valor;
            }
            $eventos['evento'] = $idEvento;
        }

        return $eventos;
    }
}
