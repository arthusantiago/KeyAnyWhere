<?php
declare(strict_types=1);

namespace App\Model\Custom;

use App\Log\Seguranca;
use Cake\Validation\Validator;
use voku\helper\AntiXSS;
use function Cake\I18n\__;

class ValidatorKaw extends Validator
{
    /**
     * Método que recebe o nome do método Validator (maxLength, requirePresence e etc )
     * e retorna a mensagem de erro padrão do sistema.
     *
     * @access public static
     * @param string $nomeMetodo Metodos de validação da class App\Model\Custom\ValidatorKaw
     * @param array<int, string|int> $valores Utilizado na tradução da mensagem
     * @return string
     */
    public static function mensagemErroPadrao(string $nomeMetodo, array $valores = []): string
    {
        $mensagens = [
            // Validator
            'checkXSS' => 'Uma string maliciosa do tipo XSS foi detectada no campo {0}',
            'requirePresence' => 'O campo {0} precisa ser informado',
            'notEmptyString' => 'O campo {0} não pode estar vazio',
            'maxLength' => 'O campo {0} pode ter no máximo {1} caracteres',
            'minLength' => 'O campo {0} precisa ter no mínimo {1} caracteres',
            'integer' => 'O campo {0} precisa ser um número inteiro',
            'boolean' => 'O campo {0} precisa ser um valor booleano (true ou false)',
            'email' => 'O campo {0} precisa ser um e-mail válido',
            'date' => 'O campo {0} precisa ser uma data válida',
            'datetime' => 'O campo {0} precisa ser uma data e hora válidas',
            'time' => 'O campo {0} precisa ser um horário válido',
            'decimal' => 'O campo {0} precisa ser um número decimal',
            'numeric' => 'O campo {0} precisa ser um valor numérico',
            'url' => 'O campo {0} precisa ser uma URL válida',
            'urlWithProtocol' => 'O campo {0} precisa ser uma URL válida e conter o protocolo (http:// ou https://)',
            'inList' => 'O valor do campo {0} não é permitido',
            'uuid' => 'O campo {0} precisa ser um UUID válido',
            'ip' => 'O IP informado é inválido',
            // RulesChecker
            'existsIn' => 'O valor do campo {0} não foi encontrado em {1}',
            'isUnique' => 'Já existe um cadastro para o valor informado no campo {0}',
        ];

        if (!array_key_exists($nomeMetodo, $mensagens)) {
            return '';
        }

        // Se os valore informados tiverem tradução, será feita neste momento
        foreach ($valores as $key => $value) {
            if (is_string($value)) {
                $valores[$key] = __($value);
            }
        }

        return __($mensagens[$nomeMetodo], $valores);
    }

    /**
     * Verifica se no dado informado contem alguma string que cause um ataque XSS.
     * Se contiver, o dado não será limpo e a informação não será salva.
     *
     * @access public
     * @param string  $field Campo que será analisado
     * @param string  $message Mensagem que será exibida caso a regra seja quebrada
     * @param bool $when Em que momento a regra será aplicada. Default: false (create && update)
     * @return mixed
     */
    public function checkXSS(
        string $field,
        string $message = 'Uma string maliciosa do tipo XSS foi detectada',
        bool $when = false,
    ): mixed {
        $antiXss = new AntiXSS();
        $callback = function ($dado, $context) use ($antiXss) {
            $antiXss->xss_clean($dado);
            $temXSS = $antiXss->isXssFound();

            if ($temXSS) {
                Seguranca::registrar(
                    'alert',
                    Seguranca::XSS_DETECTADO,
                    'Houve a tentativa de salvar uma string possivelmente maliciosa (XSS)',
                    [
                        'tabela' => $context['providers']['table']->getAlias(),
                        'campo' => $context['field'],
                        'acao' => $context['newRecord'] ? 'criacao' : 'edicao',
                        'id_entidade' => $context['data']['id'] ?? null,
                    ],
                );
            }

            return !$temXSS;
        };

        $extra = array_filter(['on' => $when, 'message' => $message]);
        $extra += ['rule' => $callback];

        return $this->add($field, 'checkXSS', $extra);
    }
}
