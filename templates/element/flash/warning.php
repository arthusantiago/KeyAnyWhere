<?php
  /**
   * Template para exibição de mensagens de alerta ao usuário.
   * Pode ser passado a esse template um array de mensagens dentro da variável $params['mensagens']
   * Exemplo: 
   * $this->Flash->warning('Cuidado, vai dar problema',['params'=>['mensagens'=> ['Problema 1','Problema 2']]]);
   *
   * @param array|null $params Variável padrão do Flash usada para passar dados ao template.
   * @param string $message Variável padrão do Flash contendo o texto que será exibido ao usuário.
 * @var \App\View\AppView $this
 * @var string $message
 * @var array $params
   */
?>
<div class="alert alert-warning" role="alert" id="alert-do-sistema">
    <?=$message?>
    <?php if(isset($params['mensagens']) && is_array($params['mensagens'])):?>
        <ul>
            <?php foreach($params['mensagens'] AS $arrayMensagens):?>
                <?php foreach($arrayMensagens AS $mensagem):?>
                    <li><?=h($mensagem)?></li>
                <?php endforeach;?>
            <?php endforeach;?>
        </ul>
    <?php endif;?>
    <button type="button" class="btn" data-bs-dismiss="alert" aria-label="Close">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
