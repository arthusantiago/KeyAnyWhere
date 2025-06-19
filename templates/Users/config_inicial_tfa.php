<?php
/**
 * @var \App\View\AppView $this
 * @var string $strSvgQrCode
 * @var \App\Model\Entity\User $user
 */
?>
<br>
<h1 class="text-center">Bem-vindo KeyAnyWhere!</h1>
<p class="text-center">Neste passo a passo vamos executar a configuração inicial da ferramenta.</p>
<br>
<h2 class="text-center titulo">Autenticação de dois fatores (TFA)</h2>
<div class="row justify-content-center">
    <p class="text-center">
        A utilização do TFA é obrigatória, por isso instale um aplicativo que execute essa função, como o <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" class="text-decoration-none" target="_blank">Google Authenticator</a>. <br>
        Para ativar o 2FA do usuário <strong><?=h($user->username)?> (<?=h($user->email)?>)</strong>, escaneie o QR Code com o aplicativo de TFA.
    </p>
</div>
<div class="row justify-content-center"><?=$strSvgQrCode?></div>
<?= $this->Form->create(null, ['url' => ['controller' => 'users', 'action' => 'configInicialTfa']]); ?>
<?php $this->Form->secure(['id', 'tfa']); ?>
    <input type="hidden" name="id" value="<?=$user->id?>"/>
    <input type="hidden" name="tfa" value="1"/>
    <div class="row justify-content-center">
        <div class="col-sm-2">
            <?= $this->element('Diversos/btnSalvar', ['parametros' => ['texto' => 'Finalizar configuração']]) ?>
        </div>
    </div>
<?= $this->Form->end(['data-type' => 'hidden']); ?>
