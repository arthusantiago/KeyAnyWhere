<?php
declare(strict_types=1);

namespace App\Log;

use App\Model\Entity\User;
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
 * 2. A classe src/Log/GerenciadorEventos fica responsável por receber as informações, criar o evento e tranforma-lo em um log.
 *
 * Para a classificar a severidade do log, é utilizado o conceito de progressão no nível do Syslog.
 *
 * @see https://en.wikipedia.org/wiki/Syslog#Severity_level
 * @global
 */
class Evento
{
    private $id;
    private $nivelSeveridadeString;
    private $nivelSeveridade;
    private $mensagem;

    /**
     * @var \Cake\Http\ServerRequest
     */
    private ServerRequest $request;
    private $recurso;
    private $ipOrigem;
    private $usuario = 'Sem informações de usuário';
    private $eventoGatilho = [];

    /**
     * Ler a documentação da classe App\Log\GerenciadorEventos::$catalogoEventos para entender
     * como catalogar um evento e de quais informações ele é composto.
     *
     * $complemento Pode ser fornecido dados adicionais para construir o Evento:
     * [
     *      'usuario' => '', // Ler a documentação do método $this->setUsuario()
     *      'request' => '', // Ler a documentação do método $this->setRequest()
     *      'recurso' => '', // Ler a documentação do método $this->setRecurso()
     *      'mensagem' => '', // Um texto adicional a mensagem default do evento.
     * ]
     *
     * @param string $idEvento Exemplo: C2-3
     * @param string|int $nivelSeveridade Ler documentação App\Log\LogLevelInt
     * @param string $mensagem
     * @param array $complemento Informações complementares
     * @param array $eventoGatilho = []
     */
    function __construct(
        string $idEvento,
        string|int $nivelSeveridade,
        string $mensagem,
        array $complemento = [],
        array $eventoGatilho = [],
    ) {
        $this->setId($idEvento);
        $this->setMensagem($mensagem, $complemento);
        $this->setNivelSeveridade($nivelSeveridade);
        $this->setEventoGatilho($eventoGatilho);

        if (isset($complemento['usuario'])) {
            $this->setUsuario($complemento['usuario']);
        }

        if (isset($complemento['request'])) {
            $this->setRequest($complemento['request']);
        }

        if (isset($complemento['recurso'])) {
            $this->setRecurso($complemento['recurso']);
        }

        if ($eventoGatilho) {
            $this->setEventoGatilho($eventoGatilho);
        }
    }

    /**
     * Seta o ID do evento, exemplo: C2-3
     *
     * @access public
     * @param string  $id
     * @return void
     */
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
     * @access public
     * @param string|int  $nivelSeveridade Exemplo: 0 ou 'EMERGENCY'
     * @return void
     */
    public function setNivelSeveridade(string|int $nivelSeveridade): void
    {
        if (is_int($nivelSeveridade)) {
            $this->nivelSeveridadeString = LogLevelInt::toString($nivelSeveridade);
            $this->nivelSeveridade = $nivelSeveridade;
        } elseif (is_string($nivelSeveridade)) {
            $this->nivelSeveridade = LogLevelInt::toNumeric($nivelSeveridade);
            $this->nivelSeveridadeString = strtolower($nivelSeveridade);
        }
    }

    /**
     * Retorna o nivel de severidade do evento conforme o tipo escolhido.
     *
     * @access public
     * @param int $tipo   1 > numeric, 2 > string
     * @return mixed
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

    /**
     * Seta o texto da mensagem que descreve o evento ocorrido.
     *
     * @access public
     * @param string  $mensagem
     * @param array   $complemento
     * @return void
     */
    public function setMensagem(string $mensagem, array $complemento = []): void
    {
        if (isset($complemento['mensagem'])) {
            $mensagem .= $complemento['mensagem'];
        }
        $this->mensagem = $mensagem;
    }

    public function getMensagem(): string
    {
        return $this->mensagem;
    }

    /**
     * Seta a request e as outras informações importantes;
     *
     * @access public
     * @param \App\Log\Cake\Http\ServerRequest $request
     * @return void
     */
    public function setRequest(ServerRequest $request): void
    {
        $this->request = $request;
        $this->setRecurso($request->getPath());
        $this->ipOrigem = $request->clientIp();
    }

    public function getRequest(): ServerRequest
    {
        return $this->request;
    }

    /**
     * Seta o recurso que foi acessado, exemplo: categorias/edit/2
     *
     * @access public
     * @param string  $recurso
     * @return void
     */
    public function setRecurso(string $recurso): void
    {
        $this->recurso = $recurso;
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
     * @access public
     * @param \App\Model\Entity\User|array|string $user
     * @return void
     */
    public function setUsuario(string|array|User $user): void
    {
        if ($user instanceof User) {
            $this->usuario = 'ID: ' . $user->id . ' | usuario: ' . $user->username . ' | e-mail:' . $user->email;
        }

        if (is_array($user)) {
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
     * @access public
     * @param string  $idEvento
     * @return mixed
     */
    public function aguardaPeloEvento(string $idEvento): bool
    {
        return in_array($idEvento, $this->getEventoGatilho());
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
