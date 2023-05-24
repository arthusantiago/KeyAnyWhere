<?php

namespace App\Log\Gerenciador;

use App\Log\Eventos;
use App\Log\EventosComplexos;
use App\Model\Entity\User;
use Cake\Http\ServerRequest;
use Cake\Core\Exception\CakeException;
use Cake\Log\Log;

/**
 * Classe que organiza as informações do evento e encaminha para criar o log.
 *
 * @global
 */
class GerenciamentoLogs
{
    /**
     * Recebe a notificação que um evento aconteceu. Esse evento contém as informações que darão origem a um registro de log.
     * Catálogo dos evento em App\Log\Eventos
     *
     * O parâmetro de entrada pode ter as seguintes chaves/valor:
     * [
     *      'evento' => '(ID do evento)' -- Obrigatório
     *      'request' => Leia a documentação do método GerenciamentoLogs::informacoesRequest()
     *      'usuario' => Leia a documentação do método GerenciamentoLogs::informacoesUsuario()
     * ]
     *
     * @access	public static
     * @param	array $dados
     * @return	void
     */
    public static function novoEvento(array $dados)
    {
        if (empty($dados['evento'])) {
            throw new CakeException('O ID do evento não foi informado');
        }

        $evento = Eventos::getEvento($dados['evento']);
        $evento['nivel_severidade'] = LogLevelInt::toNumeric($evento['nivel_severidade_string']);

        if (isset($dados['usuario'])) {
            $evento['usuario'] = self::informacoesUsuario($dados['usuario']);
        }

        if (isset($dados['request'])) {
            $evento = self::informacoesRequest($dados['request'], $evento);
        }

        if (self::criarLog($evento)) {
            (new EventosComplexos)->novoLogSalvo($evento);
        }
    }

    /**
     * Monta a string com as informações do usuário.

     * Exemplo de uso:
     *
     * # array
     * GerenciamentoLogs::novoEvento([
     *      'evento' => 'C2-1',
     *      'usuario' => [
     *          'dados' => [ 'user' => 'fulano@fulano.com', 'nome' => 'Fulano de tal']
     *          'texto' => 'O usuário tenta logar utilizando os seguintes dados: '
     *      ]
     * ]);
     *
     * # string
     * GerenciamentoLogs::novoEvento([
     *      'evento' => 'C2-1',
     *      'usuario' => 'O usuário Fulano de Tal fez algo errado'
     * ]);
     *
     * # App\Model\Entity\User
     * GerenciamentoLogs::novoEvento([
     *      'evento' => 'C2-1',
     *      'usuario' =>  (Um objeto App\Model\Entity\User preenchido)
     * ]);
     *
     * @access	private static
     * @param	null|array|string|App\Model\Entity\User $user
     * @return	string
     */
    private static function informacoesUsuario(mixed $user): string
    {
        $retorno = 'Sem informações de usuário';

        if ($user instanceof User) {
            $retorno = 'ID: ' . $user->id . ' | usuario: ' . $user->username . ' | e-mail:' . $user->email;
        }

        if (is_array($user)) {
            $retorno = $user['texto'] . implode(', ', $user['dados']);
        }

        if (is_string($user) && !empty($user)) {
            $retorno = $user;
        }

        return $retorno;
    }

    /**
     * Extrai do objeto Cake\Http\ServerRequest as informações importantes da request
     *
     * @access	private static
     * @param	Cake\Http\ServerRequest	$request
     * @param	array $evento O evento que receberá as informaçoes da request.
     * @return	array
     */
    private static function informacoesRequest(ServerRequest $request, array $evento): array
    {
        $evento['recurso'] = $request->getPath();
        $evento['ip_origem'] = $request->clientIp();

        return $evento;
    }

    /**
     * Recebe o evento e utiliza as informações para criar o log. O log está sendo salvo no BD.
     * Ler o arquivo kaw/config/app.php['Log']['database'] e /kaw/src/Log/Engine/DatabaseLog.php
     *
     * @access	private static
     * @param	array	$informacoesEvento
     * @return	bool Sucesso ou falha no processo de salvar o log.
     */
    private static function criarLog(array $evento): bool
    {
        return Log::write(
            $evento['nivel_severidade_string'],
            $evento['mensagem'],
            [
                'scope' => ['atividades'],
                'dados' => $evento
            ]
        );
    }
}
