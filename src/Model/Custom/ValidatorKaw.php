<?php
declare(strict_types=1);

namespace App\Model\Custom;

use App\Log\Seguranca;
use Cake\Validation\Validator;
use voku\helper\AntiXSS;

class ValidatorKaw extends Validator
{
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
