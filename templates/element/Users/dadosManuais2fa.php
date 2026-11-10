<?php
/**
 * Dados para cadastrar o 2FA manualmente no aplicativo autenticador, para quem não conseguir escanear o QrCode.
 * Sem $chave, o bloco é renderizado oculto e preenchido via JS (obterQrCode2FA() em buscaConteudo.js).
 *
 * @var \App\View\AppView $this
 * @var string|null $chave Chave secreta do 2FA
 * @var string|null $codinome Nome do registro no aplicativo de 2FA
 */
$chave ??= null;
$codinome ??= null;
?>
<div id="dadosManuais2FA" class="row justify-content-center mt-3<?= $chave ? '' : ' d-none' ?>">
    <div class="col-sm-auto">
        <p class="text-center mb-2">
            <?=__('Não consegue escanear o QrCode? Cadastre manualmente no aplicativo com os dados abaixo')?>:
        </p>
        <table class="table table-md">
            <tr>
                <th scope="row"><?=__('Chave')?></th>
                <td><code id="chave2FA" class="user-select-all"><?= h($chave) ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?=__('Tipo de chave')?></th>
                <td><?=__('Baseada no horário')?></td>
            </tr>
            <tr>
                <th scope="row"><?=__('Codinome')?></th>
                <td>
                    <span id="codinome2FA"><?= h($codinome) ?></span><br>
                    <small class="text-body-secondary"><?=__('Nome do registro no aplicativo de 2FA')?></small>
                </td>
            </tr>
        </table>
    </div>
</div>
