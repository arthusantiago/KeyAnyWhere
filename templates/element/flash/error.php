<?php
  /**
   * Element para exibição de mensagens de erro ao usuário.
   * Pode ser passado um array de mensagens dentro da variável $params['mensagens'].
   *
   * Exemplo:
   *    $this->Flash->error('Erro ao salvar', ['params' => ['mensagens' => $artigos->getErrors()]]);
   *    $this->Flash->error('Erro ao salvar', ['params' => ['mensagens' => ['Primeiro error', 'Segundo erro']]]);
   *
   * Também pode ser passado no índice $params['entidades'] um array de entidades com erros.
   *
   * Exemplo:
   *    $this->Flash->error('Erro ao cadastrar', ['params' => ['entidades' => [ $entidade1, $entidade2 ]]]);
   *
   * @param array|null $params Variável padrão do Flash usada para passar dados ao template.
   * @param string $message Variável padrão do Flash contendo o texto que será exibido ao usuário.
 * @var \App\View\AppView $this
 * @var mixed $msg
 * @var array $params
 * @var mixed $tipoErro
   */

//array de mensagens de erro
$mensagensErro = [];

$getErro = function ($msg, $tipoErro) use (&$mensagensErro)
{
    $mensagensErro[] = $msg;
};

// Para um array de entidades
if(isset($params['entidades']) && is_array($params['entidades']))
{
    foreach($params['entidades'] as $entidade)
    {
        $erros = $entidade->getErrors();
        array_walk_recursive($erros, $getErro);
    }
}

// Para um array de mensagens simples
if(isset($params['mensagens']) && is_array($params['mensagens']))
{
    array_walk_recursive($params['mensagens'], $getErro);
}

//se a variável '$message' for nula é atribuído a ela um texto padrão.
if (empty($message))
{
    $message = 'Ocorreu um erro: ';
}
?>

<div class="alert alert-danger" role="alert" id="alert-do-sistema">
    <div class="row">
        <div class="col-sm"><?=$message?></div>
        <div class="col-sm-auto text-end">
            <button type="button" class="btn" data-bs-dismiss="alert" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
    <?php if($mensagensErro): ?>
        <ul>
            <?php foreach($mensagensErro as $mensagem):?>
                <li><?=h($mensagem)?></li>
            <?php endforeach;?>
        </ul>
    <?php endif; ?>
</div>
