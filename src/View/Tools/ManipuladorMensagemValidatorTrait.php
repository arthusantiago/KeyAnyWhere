<?php
declare(strict_types=1);

namespace App\View\Tools;

/**
 * Códigos utilizados para manipulação de mensagens de erro,
 * decorrentes da aplicação de um Validator (\App\Model\Custom\ValidatorKaw)
 */
trait ManipuladorMensagemValidatorTrait
{
    /**
     * Deve ser informado no índice 'mensagens' um array de mensagens. Exemplo:
     * $this->extraiMensagens(['mensagens' => $artigos->getErrors()]);
     * $this->extraiMensagens(['mensagens' => ['Primeiro error', 'Segundo erro']]);
     *
     * Também pode ser informado no índice 'entidades', entidades com erros. Exemplo:
     * $this->extraiMensagens(['entidades' => [$entidade1, $entidade2 ]]);
     *
     * @param array<string, mixed> $params
     * @return array<int, string>
     */
    public function extraiMensagens(array $params): array
    {
        if (!isset($params['mensagens']) && !isset($params['entidades'])) {
            return [];
        }

        $mensagensErro = [];

        $funcGetErro = function ($msg) use (&$mensagensErro): void {
            $mensagensErro[] = $msg;
        };

        if (isset($params['entidades']) && is_array($params['entidades'])) {
            foreach ($params['entidades'] as $entidade) {
                $erros = $entidade->getErrors();
                array_walk_recursive($erros, $funcGetErro);
            }
        }

        if (isset($params['mensagens']) && is_array($params['mensagens'])) {
            array_walk_recursive($params['mensagens'], $funcGetErro);
        }

        return $mensagensErro;
    }

    /**
     * Adiciona ao inicio de cada mensagem, um numero para identificação.
     *
     * @access public
     * @param array<int, string> $erros Exemplo: ['Erro de tal', 'Erro de fulano']
     * @return array<int, string> Exemplo: ['1- Erro de tal', '2- Erro de fulano']
     */
    public function adicionaNumerador(array $erros): array
    {
        foreach ($erros as $key => $value) {
            $erros[$key] = ($key + 1) . '- ' . $value;
        }

        return $erros;
    }

    /**
     * Recebe um array de mensagens e retorna um unica string com todas as mensagens, separadas por '$separador'
     *
     * @access public
     * @param array<int, string> $erros
     * @param string $separador
     * @return string
     */
    public function concatenaMensagens(array $erros, string $separador = ' | '): string
    {
        return implode($separador, $erros);
    }
}
