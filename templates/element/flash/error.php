<?php

/**
 * Element para exibição de mensagens de erro ao usuário.
 * Ler a documentação de App\View\Tools\ManipuladorMensagemValidatorTrait para mais informações sobre o parâmetro $params.
 *
 * @param array|null $params Variável padrão do Flash usada para passar dados ao template.
 * @param string $message Variável padrão do Flash contendo o texto que será exibido ao usuário.
 * @var \App\View\AppView $this
 */

$mensagensErro = $this->extraiMensagens($params);
$message = $message ?: 'Ocorreu um erro';
?>

<div class="alert alert-danger" role="alert" id="alert-do-sistema">
    <div class="row">
        <div class="col-sm"><?= __(h($message)) ?></div>
        <div class="col-sm-auto text-end">
            <button type="button" class="btn" data-bs-dismiss="alert" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
    <?php if ($mensagensErro): ?>
        <ul>
            <?php foreach ($mensagensErro as $mensagem): ?>
                <li><?= __(h($mensagem)) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>