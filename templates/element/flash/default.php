<?php
  /**
   * Template default para exibição de mensagens ao usuário.
   * Pode ser passado a esse template um array de mensagens dentro da variável $params['mensagens']
   * Exemplo: 
   * $this->Flash->set('Bem-vindo Fulano de Tal, temos novidades:',['params'=>['mensagens'=> ['Novo App','Mais espaço']]]);
   *
   * @param array|null $params Variável padrão do Flash usada para passar dados ao template.
   * @param string $message Variável padrão do Flash contendo o texto que será exibido ao usuário.
   */
?>
<div class="alert alert-primary" role="alert" id="alert-do-sistema">
    <?=$message?>
    <?php if(isset($params['mensagens']) && is_array($params['mensagens'])):?>
        <ul>
            <?php foreach($params['mensagens'] AS $arrayMensagens):?>
                <?php foreach($arrayMensagens AS $mensagem):?>
                    <li><?=$mensagem?></li>
                <?php endforeach;?>
            <?php endforeach;?>
        </ul>
    <?php endif;?>
</div>
