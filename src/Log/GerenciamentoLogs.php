<?php

namespace App\Log;

use Cake\Log\Log;
use Cake\Http\ServerRequest;
use App\Model\Entity\User;
use Cake\Core\Exception\CakeException;

class GerenciamentoLogs
{
    /**
     * Contem as propriedades que são comuns entre todos os eventos.
     *
     * @var	array $propComunEventos
     */
    private static $propComunEventos = [
        'nivel_severidade' => null,
        'recurso' => null,
        'ip_origem' => null,
        'usuario' => null,
    ];

    private static $eventos = [
        'C1-1' => [
            'nivel_severidade_string' => 'notice',
            'mensagem' => 'Durante o processo de login o usuario errou o user ou password'
        ],
        'C1-2' => [
            'nivel_severidade_string' => 'notice',
            'mensagem' => 'Durante o processo de login o usuario errou o 2FA'
        ],
        'C1-3' => [
            'nivel_severidade_string' => 'warning',
            'mensagem' => 'Durante o processo de login, o usuário erro mais de 3 vezes o user, password ou 2FA.'
        ],
        'C1-4' => [
            'nivel_severidade_string' => 'alert',
            'mensagem' => 'Durante o processo de login, o usuario excedeu a quantidade maxima de tentativas erradas.'
        ],
        'C2-1' => [
            'nivel_severidade_string' => 'warning',
            'mensagem' => 'O usuário tentou acessar um recurso que somente o root tem permissão.'
        ],
    ];

    /**
     * Recebe a notificação que um evento aconteceu. Esse evento dará origem a um registro de log.
     *
     * @access	public static
     * @param	string $idEvento
     * @return	void
     */
    public static function novoEvento(string $idEvento, array $dados)
    {
        $informacoesEvento = self::preparaInformacoesLog($idEvento, $dados);
        self::salvarLog($informacoesEvento);
    }

    /**
     * Metodo que monta toda a estrutura do evento que será convertido em um log.
     *
     * É esperado na variável $dados as seguintes chaves/valor:
     * [
     *      'request' => $obj, -> Um objeto do tipo Cake\Http\ServerRequest
     *      'usuario' => $obj, -> Um objeto do tipo App\Model\Entity\User
     * ]
     *
     * @access	private static
     * @param	string	$idEvento Identificado do evento
     * @param	mixed 	$dados Contendo as informações necessário para montagem da estrutura do evento.
     * @return	array
     */
    private static function preparaInformacoesLog(string $idEvento, array $dados): array
    {
        if ($dados['usuario'] instanceof User == false) {
            throw new CakeException('O objeto informado em $dados["usuario"] precisa ser do tipo App\Model\Entity\User');
        }

        if ($dados['request'] instanceof ServerRequest == false) {
            throw new CakeException('O objeto informado em $dados["request"] precisa ser do tipo Cake\Http\ServerRequest');
        }

        $idEvento = strtoupper($idEvento);
        if (array_key_exists($idEvento, self::$eventos) == false) {
            throw new CakeException('O evento ' . $idEvento . ' não existe no catálogo de eventos mapeados');
        }

        self::setandoPropriedadesComunsEventos($idEvento);
        $descricaoEvento = self::$eventos[$idEvento];
        $descricaoEvento['nivel_severidade'] = LogLevelInt::strLevelToNumeric($descricaoEvento['nivel_severidade_string']);
        $descricaoEvento['recurso'] = $dados['request']->getPath();
        $descricaoEvento['ip_origem'] = $dados['request']->clientIp();
        if (isset($dados['usuario'])) {
            $descricaoEvento['usuario'] = self::informacoesUsuario($dados['usuario']);
        }

        return $descricaoEvento;
    }

    /**
     * Atribui na estrutura do evento as propriedades que são comuns entre todos os evento.
     *
     * @access	private static
     * @param	string	$idEvento
     * @return	void
     */
    private static function setandoPropriedadesComunsEventos(string $idEvento): void
    {
        foreach (self::$propComunEventos as $propriedade => $valor) {
            self::$eventos[$idEvento][$propriedade] = $valor;
        }
    }

    /**
     * Monta a string com as informações importantes do usuário.
     *
     * @access	private static
     * @param	user	$user
     * @return	mixed
     */
    private static function informacoesUsuario(User $user): string
    {
        return 'ID: ' . $user->id . ' | usuario: ' . $user->username . ' | e-mail:' . $user->email;
    }

    /**
     * Recebe as informações do evento e salva como log.
     *
     * @access	private static
     * @param	array	$informacoesEvento
     * @return	bool Sucesso ou falha no processo de salvar o log.
     */
    private static function salvarLog(array $informacoesEvento): bool
    {
        return Log::write(
            $informacoesEvento['nivel_severidade_string'],
            $informacoesEvento['mensagem'],
            [
                'scope' => ['atividades'],
                'dados' => $informacoesEvento
            ]
        );
    }
}
