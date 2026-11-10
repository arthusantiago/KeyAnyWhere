<?php

/**
 * Template para exibição de mensagens de sucesso ao usuário.
 * Pode ser passado a esse template um array de mensagens dentro da variável $params['mensagens']
 * Exemplo: 
 * $this->Flash->success('Salvo com sucesso',['params'=>['mensagens'=> ['Agora ví vantage!','Vai que é tua Taffarel!']]]);
 *
 * @param array|null $params Variável padrão do Flash usada para passar dados ao template.
 * @param string $message Variável padrão do Flash contendo o texto que será exibido ao usuário.
 * @var \App\View\AppView $this
 * @var string $message
 * @var array $params
 */

//se a variável '$message' for nula é atribuído a ela um texto padrão.
if (empty($message)) {
    $message = 'Salvo com sucesso';
}
?>
<div class="alert alert-success" role="alert" id="alert-do-sistema">
    <?= __(h($message)) ?>
    <?php if (isset($params['mensagens']) && is_array($params['mensagens'])): ?>
        <ul>
            <?php foreach ($params['mensagens'] as $arrayMensagens): ?>
                <?php foreach ($arrayMensagens as $mensagem): ?>
                    <li><?= __(h($mensagem)) ?></li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <button type="button" class="btn" data-bs-dismiss="alert" aria-label="Close">
        <i class="bi bi-x-lg"></i>
    </button>
</div>