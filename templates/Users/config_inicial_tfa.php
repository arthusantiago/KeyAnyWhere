<?php
/**
 * @var \App\View\AppView $this
 * @var string $strSvgQrCode Contendo o SVG do QRCode
 * @var string $chave2fa Chave do 2FA, para cadastro manual
 * @var string $codinome2fa Nome do registro no aplicativo de 2FA, para cadastro manual
 * @var \App\Model\Entity\User $user
 */
?>

<div class="row">
    <div class="col-sm mb-3">
        <h2 class="text-center titulo">(2 de 2) <?=__('Segundo fator de autenticação')?> (2FA)</h2>
    </div>
</div>
<div class="row">
    <div class="col-sm">
        <p class="text-center">
            <?=__('A utilização do 2FA é obrigatória, por isso instale um aplicativo que execute essa função, como o')?> <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" class="text-decoration-none" target="_blank">Google Authenticator</a>.<br>
            <?php echo __(
                'Para ativar o Segundo Fator de Autenticação do usuário <strong>{0} ({1})</strong>, escaneie o QRCode com o aplicativo de 2FA escolhido',
                [h($user->username), h($user->email)]
            );?>.
        </p>
    </div>
</div>

<div class="row justify-content-center"><?=$strSvgQrCode?></div>

<?= $this->element('Users/dadosManuais2fa', ['chave' => $chave2fa, 'codinome' => $codinome2fa]) ?>

<div class="row">
    <div class="col-sm ">
        <p class="text-center">
            <?=__('Não se preocupe se não puder escanear o QRCode agora. Você poderá abrir e fechar esta tela a qualquer momento')?>.<br>
            <?=__('No entanto, <strong>clique no botão abaixo apenas depois de escanear o QRCode (ou cadastrar a chave manualmente)</strong>, pois eles <strong>não serão exibidos novamente</strong>')?>.
        </p>
    </div>
</div>

<?= $this->Form->create(null, ['url' => ['controller' => 'users', 'action' => 'configInicialTfa']]); ?>
<?php $this->Form->secure(['tfa']); ?>
    <input type="hidden" name="tfa" value="1"/>
    <div class="row justify-content-center">
        <div class="col-sm-2">
            <?= $this->element('Diversos/btnSalvar', ['parametros' => ['texto' => 'Completar configuração']]) ?>
        </div>
    </div>
<?= $this->Form->end(['data-type' => 'hidden']); ?>
