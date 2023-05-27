<?php

namespace App\Log;

use App\Log\LogLevelInt;
use App\Model\Entity\User;
use Cake\Core\Exception\CakeException;
use Cake\Http\ServerRequest;

/**
 * Um evento é uma situação/acontecimento que já foi mapeada e definida suas propriedades.
 * Esse evento envolve algo que o sistema precisa monitorar. Quando o evento acontece, ele deve ser
 * notificado e transformado em um registro de log.
 *
 * Abaixo está descrito o fluxo criação de um evento até transformá-lo em um registro de log:
 *
 * 1. Acontece um evento. Quem o identifica também fica responsável por notificá-lo.
 *    Quem notifica o evento também deve informar todos os dados necessários.
 * 2. A classe /kaw/src/Log/GerenciamentoLogs.php fica responsável receber as informações, criar o evento e tranforma-lo em um log.
 *
 * Para a classificar a severidade do log, é utilizado o conceito de progressão no nível do Syslog.
 * @see https://en.wikipedia.org/wiki/Syslog#Severity_level
 * @global
 */
class Evento
{
    private $id;
    private $nivelSeveridadeString;
    private $nivelSeveridade;
    private $mensagem;
    private $request;
    private $recurso;
    private $ipOrigem;
    private $usuario;
    private $eventoGatilho = [];

    /**
     * Ler a documentação da classe App\Log\Eventos para entender como catalogar um evento.
     *
     * @access	public
     * @param	array	$infoBasicas
     * @param	array	$dados      	Default: null
     * @return	void
     */
    public function __construct(array $infoBasicas, array $dados = null)
    {
        if (empty($infoBasicas['evento'])) {
            throw new CakeException('O ID do evento não foi informado');
        }

        if (empty($infoBasicas['nivel_severidade'])) {
            throw new CakeException('O Nível de Severidade não foi informado');
        }

        if (empty($infoBasicas['mensagem'])) {
            throw new CakeException('O ID do evento não foi informado');
        }

        $this->setId($infoBasicas['evento']);
        $this->setMensagem($infoBasicas['mensagem']);
        $this->setNivelSeveridade($infoBasicas['nivel_severidade']);

        if (isset($infoBasicas['evento_gatilho'])) {
            $this->setEventoGatilho($infoBasicas['evento_gatilho']);
        }

        if (isset($dados['request'])) {
            $this->setRequest($dados['request']);
        }

        if (isset($dados['usuario'])) {
            $this->setUsuario($dados['usuario']);
        }
    }

    public function setId(string $id): void
    {
        $this->id = strtoupper($id);
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Atribui o Nivel de severidade em formato numerico e textual.
     *
     * @access	public
     * @param	string|int	$nivelSeveridade Exemplo: 0 ou 'EMERGENCY'
     * @return	void
     */
    public function setNivelSeveridade($nivelSeveridade): void
    {
        if (is_int($nivelSeveridade)) {
            $this->nivelSeveridadeString = LogLevelInt::toString($nivelSeveridade);
            $this->nivelSeveridade = $nivelSeveridade;
        } else if (is_string($nivelSeveridade)) {
            $this->nivelSeveridade = LogLevelInt::toNumeric($nivelSeveridade);
            $this->nivelSeveridadeString = strtolower($nivelSeveridade);
        }
    }

    /**
     * Retorna o nivel de severidade do evento conforme o tipo escolhido.
     *
     * @access	public
     * @param	integer	$tipo	1 > numeric, 2 > string
     * @return	mixed
     */
    public function getNivelSeveridade(int $tipo = 1): mixed
    {
        switch ($tipo) {
            case 1:
                $nivelSeveridade = $this->nivelSeveridade;
                break;
            case 2:
                $nivelSeveridade = $this->nivelSeveridadeString;
                break;
            default:
                $nivelSeveridade = $this->nivelSeveridade;
                break;
        }

        return $nivelSeveridade;
    }

    public function setMensagem(string $mensagem): void
    {
        $this->mensagem = $mensagem;
    }

    public function getMensagem(): string
    {
        return $this->mensagem;
    }

    /**
     * Seta a request e as outras informações importantes;
     *
     * @access	public
     * @param	Cake\Http\ServerRequest	$request
     * @return	void
     */
    public function setRequest(ServerRequest $request): void
    {
        $this->request = $request;
        $this->recurso = $request->getPath();
        $this->ipOrigem = $request->clientIp();
    }

    public function getRequest(): ServerRequest
    {
        return $this->request;
    }

    public function getRecurso(): string
    {
        return $this->recurso;
    }

    public function getIpOrigem(): string
    {
        return $this->ipOrigem;
    }

    /**
     * Monta a string com as informações do usuário.
     * Exemplo de uso:
     *
     * ## string
     * GerenciadorEventos::notificarEvento([
     *      'evento' => 'C2-1',
     *      'usuario' => 'O usuário Fulano de Tal fez algo errado'
     * ]);
     *
     * ## array
     * GerenciadorEventos::notificarEvento([
     *      'evento' => 'C2-1',
     *      'usuario' => [
     *          'dados' => [ 'user' => 'fulano@fulano.com', 'nome' => 'Fulano de tal']
     *          'texto' => 'O usuário tenta logar utilizando os seguintes dados: '
     *      ]
     * ]);
     *
     * ## App\Model\Entity\User
     * GerenciadorEventos::notificarEvento([
     *      'evento' => 'C2-1',
     *      'usuario' =>  App\Model\Entity\User
     * ]);
     *
     * @access	public
     * @param	string	$user	Default: 'Sem informações de usuário'
     * @return	void
     */
    public function setUsuario(mixed $user = 'Sem informações de usuário'): void
    {
        $this->usuario = $user;

        if ($user instanceof User) {
            $this->usuario = 'ID: ' . $user->id . ' | usuario: ' . $user->username . ' | e-mail:' . $user->email;
        }

        if (is_array($user))
        {
            $dados = '';
            foreach ($user['dados'] as $key => $value) {
                if (is_int($key)) {
                    $dados .= $value . ', ';
                } else {
                    $dados .= $key . ': ' . $value . ', ';
                }
            }
            $this->usuario = $user['texto'] . rtrim($dados, ', ');
        }

        if (is_string($user)) {
            $this->usuario = $user;
        }
    }

    public function getUsuario(): string
    {
        return $this->usuario;
    }

    public function setEventoGatilho(array $eventoGatilho): void
    {
        $this->eventoGatilho = $eventoGatilho;
    }

    public function getEventoGatilho(): array
    {
        return $this->eventoGatilho;
    }

    /**
     * Verifica se o evento informado é um Evento Gatilho aguardado.
     *
     * @access	public
     * @param	string	$idEvento
     * @return	mixed
     */
    public function aguardaPeloEvento(string $idEvento): bool
    {
        return in_array($idEvento, $this->eventoGatilho);
    }

    public function toArray(): array
    {
        return [
            'evento' => $this->id,
            'nivel_severidade' => $this->nivelSeveridade,
            'recurso' => $this->recurso,
            'ip_origem' => $this->ipOrigem,
            'usuario' => $this->usuario,
            'mensagem' => $this->mensagem,
        ];
    }
}
