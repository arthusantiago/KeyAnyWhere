<?php

namespace App\Log;

use Cake\Log\Log;
use App\Log\Evento;
use App\Log\EventosComplexos;
use Cake\Core\Exception\CakeException;

class GerenciadorEventos
{
    /**
     * Eventos catalogados.
     *
     * O evento deve pertencer a uma categoria, que é representada pelo 'C?' do identificador do evento.
     * As categorias agrupam eventos semelhantes.
     *
     * Estrutura de um evento:
     * 'C1-1' => [
     *      'nivel_severidade' => 'notice', // [obrigatorio] Seguir o padrão do Syslog.
     *      'mensagem' => 'Durante o login o usuario errou o user ou password', // [obrigatorio] Texto descrevendo o problema. Esse é a menssagem do log.
     *      'evento_gatilho' => ['C1-1', 'C1-2'] // [Opcional] Diz se o evento será disparado a partir do registro de outros eventos. Ler classe /kaw/src/Log/EventosComplexos.php
     *  ]
     *
     * @var array $catalogoEventos
     */
    private static $catalogoEventos = [
        /* Categoria: 1. LOGIN
         *
         * Eventos que acontecerem antes, durante e depois do login no sistema.
         */
        'C1-1' => [
            'nivel_severidade' => 'notice',
            'mensagem' => 'Durante o login o usuario errou o user ou password'
        ],
        'C1-2' => [
            'nivel_severidade' => 'notice',
            'mensagem' => 'Durante o login o usuario errou o 2FA'
        ],
        'C1-3' => [
            'nivel_severidade' => 'warning',
            'mensagem' => 'Durante o login o usuário erro mais de 3 vezes as credenciais de acesso.',
            'evento_gatilho' => ['C1-1', 'C1-2']
        ],
        'C1-4' => [
            'nivel_severidade' => 'alert',
            'mensagem' => 'Durante o login o usuário excedeu a quantidade máxima de tentativas erradas. O seu IP foi bloqueado.',
            'evento_gatilho' => ['C1-3']
        ],
        /* Categoria: 2. ACESSO NÃO AUTORIZADO
         *
         * Descreve as tentativas de acesso a algum recurso ao qual o usuário não tem permissão.
         */
        'C2-1' => [
            'nivel_severidade' => 'warning',
            'mensagem' => 'O usuário tentou acessar um recurso que somente o root tem permissão.'
        ],
    ];

    /**
     * Recebe a notificação que um evento aconteceu. Esse evento contém as informações que darão origem a um registro de log.
     *
     * O parâmetros esperados:
     * [
     *      'evento' => 'Ex: C2-3' -- Obrigatório
     *      'request' => Leia a documentação do método src/Log/Evento::setInformacoesRequest()
     *      'usuario' => Leia a documentação do método src/Log/Evento::setInformacoesUsuario()
     * ]
     *
     * @access	public static
     * @param	array $dados
     * @return	void
     */
    public static function notificarEvento(array $dados)
    {
        if (empty($dados['evento'])) {
            throw new CakeException('O ID do evento não foi informado');
        }

        $evento = self::getEvento($dados);

        if (self::criarLog($evento)) {
            (new EventosComplexos)->novoLogSalvo($evento);
        }
    }

    /**
     * Retorna o evento desejado
     *
     * @access	public static
     * @param	array	$idEvento
     * @return	Evento
     */
    public static function getEvento(array $dados): Evento
    {
        $infoBasicas = self::eventoCatalogado($dados['evento']);

        if ($infoBasicas == false) {
            throw new CakeException('O evento ' . $dados['evento'] . ' não foi catalogado. Cadastre-o em App\Log\Eventos');
        }

        return new Evento($infoBasicas, $dados);
    }

    /**
     * Retorna todos os eventos do catalogo
     *
     * @access	public static
     * @return	array
     */
    public static function getEventos(): array
    {
        $eventos = [];

        foreach (self::$catalogoEventos as $idEvento => $infoBasicas) {
            $idEvento = strtoupper($idEvento);
            $infoBasicas['evento'] = $idEvento;
            $eventos[$idEvento] = new Evento($infoBasicas);
        }

        return $eventos;
    }

    /**
     * Verifica se o evento informado se encontra no catalogo de eventos.
     *
     * @access	public static
     * @param	string	$idEvento Exemplo: 'C1-2'
     * @return	false|array Retorna o evento solicitado, 'false' caso contrario.
     */
    public static function eventoCatalogado(string $idEvento): mixed
    {
        $idEvento = strtoupper($idEvento);
        $evento = false;

        if (array_key_exists($idEvento, self::$catalogoEventos)) {
            $evento = self::$catalogoEventos[$idEvento];
            $evento['evento'] = $idEvento;
        }

        return $evento;
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
