<?php

namespace App\Log;

use Cake\Log\Log;
use App\Log\Evento;
use App\Log\EventosComplexos;
use Cake\Core\Exception\CakeException;

class GerenciadorEventos
{
    /**
     * Você pode inserir o seu evento no Catalogo de eventos.
     *
     * O evento deve pertencer a uma categoria, que é representada pelo 'C?' do identificador do evento.
     * As categorias agrupam eventos semelhantes.
     *
     * Estrutura::
     * 'C1-1' => [
     *      // [obrigatório] Seguir o padrão do Syslog.
     *      'nivel_severidade' => 'notice',
     *
     *      // [obrigatório] Texto descrevendo o problema. Esse é a mensagem do log.
     *      'mensagem' => 'Durante o login o usuario errou o user ou password',
     *
     *      // [Opcional] Diz se o evento será disparado a partir do registro de outros eventos. Ler classe src/Log/EventosComplexos.php
     *      'evento_gatilho' => ['C1-2', 'C1-3']
     *  ]
     *
     * @var array $catalogoEventos
     */
    private static $catalogoEventos = [
        /*
         * Categoria 1: LOGIN
         * Eventos que acontecerem antes, durante e depois do login no sistema.
         */
        'C1-1' => [
            'nivel_severidade' => 'notice',
            'mensagem' => 'Durante o login o usuário errou o user ou password.'
        ],
        'C1-2' => [
            'nivel_severidade' => 'notice',
            'mensagem' => 'Durante o login o usuário errou o 2FA.'
        ],
        'C1-3' => [
            'nivel_severidade' => 'warning',
            'mensagem' => 'Durante o login o usuário erro mais de 3 vezes uma das credenciais de acesso. O seu IP foi bloqueado.',
            'evento_gatilho' => ['C1-1', 'C1-2']
        ],

        /*
         * Categoria 2: ACESSO NÃO AUTORIZADO
         *
         * Descreve as tentativas de acesso a algum recurso ao qual o usuário não tem permissão.
         */
        'C2-1' => [
            'nivel_severidade' => 'warning',
            'mensagem' => 'O usuário tentou acessar um recurso que somente o root tem permissão.'
        ],

        /*
         * Categoria 3: POSSÍVEL ATAQUE
         *
         * Descreve os tipos de tentativa de ataque
         */
        'C3-1' => [ // XSS
            'nivel_severidade' => 'alert',
            'mensagem' => 'Foi detectado um possível ataque do tipo XSS.',
        ],
    ];

    /**
     * Recebe as informações necessárias criar um Evento. Esse evento dará origem a um registro de log.
     *
     * @access	public static
     * @param	array $dados
     * @return	void
     */
    public static function notificarEvento(array $dados)
    {
        $infoBasicas = self::getEvento($dados['evento']);
        $evento = new Evento(
            $infoBasicas['evento'],
            $infoBasicas['nivel_severidade'],
            $infoBasicas['mensagem'],
            $dados,
            $infoBasicas['evento_gatilho']
        );

        if (self::criarLog($evento)) {
            (new EventosComplexos)->novoLogSalvo($evento);
        }
    }

    /**
     * Busca o evento informado no catalogo de eventos.
     *
     * @access	public static
     * @param	string	$idEvento Exemplo: 'C1-2'
     * @return	array Retorna as informações básicas do evento solicitado.
     */
    public static function getEvento(string $idEvento): array
    {
        $catalogoEventos = self::getCatalogoEventos();
        $idEvento = strtoupper($idEvento);
        $evento = false;

        if (array_key_exists($idEvento, $catalogoEventos)) {
            $evento = $catalogoEventos[$idEvento];
            $evento['evento'] = $idEvento;
        }

        if (!$evento) {
            throw new CakeException('O evento \'' . $idEvento . '\' não foi encontrado no catálogo');
        }

        return $evento;
    }

    /**
     * Retorna todos os eventos do catalogo
     *
     * @access	public static
     * @return	App\Log\Evento[]
     */
    public static function getEventos(): array
    {
        $eventos = [];
        $catalogoEventos = self::getCatalogoEventos();
        foreach ($catalogoEventos as $idEvento => $infoBasicas) {
            $idEvento = strtoupper($idEvento);
            $infoBasicas['evento'] = $idEvento;
            $eventos[$idEvento] = new Evento(
                $infoBasicas['evento'],
                $infoBasicas['nivel_severidade'],
                $infoBasicas['mensagem'],
                [],
                $infoBasicas['evento_gatilho']
            );
        }

        return $eventos;
    }

    /**
     * Retorna o catalogo de eventos já padronizado.
     * Método que garante que o todos os eventos estarão no padrão esperado.
     *
     * @access	public static
     * @return	array
     */
    public static function getCatalogoEventos(): array
    {
        $catalogoEventos = self::$catalogoEventos;
        foreach ($catalogoEventos as $idEvento => $infoBasicas) {
            // Adicionando posições no array eventos, que todos precisam ter
            if (!array_key_exists('evento_gatilho', $infoBasicas)) {
                $catalogoEventos[$idEvento]['evento_gatilho'] = [];
            }

            // Padronizando o texto da mensagem
            $infoBasicas['mensagem'] = trim($infoBasicas['mensagem']);
            if (!(substr($infoBasicas['mensagem'], -1) === '.')) {
                $catalogoEventos[$idEvento]['mensagem'] = $infoBasicas['mensagem'] . '. ';
            }
        }

        return $catalogoEventos;
    }

    /**
     * Recebe o evento e utiliza as informações para criar o log. O log está sendo salvo no BD.
     * Ler o arquivo kaw/config/app.php['Log']['database'] e /kaw/src/Log/Engine/DatabaseLog.php
     *
     * @access	private static
     * @param	App\Log\Evento	$evento
     * @return	bool Sucesso ou falha no processo de salvar o log.
     */
    private static function criarLog(Evento $evento): bool
    {
        return Log::write(
            $evento->getNivelSeveridade(),
            $evento->getMensagem(),
            [
                'scope' => ['atividades'],
                'dados' => $evento
            ]
        );
    }
}
