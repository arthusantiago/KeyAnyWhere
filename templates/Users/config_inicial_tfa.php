<!doctype html>
<html lang="pt-br">
<head>
    <?= $this->Html->charset() ?>
    <?= $this->Html->meta('icon', 'favicon.ico') ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= $this->Html->meta('csrfToken', $this->request->getAttribute('csrfToken')); ?>
    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">
    <title>KAW - Configuração inicial</title>
    <?= $this->Html->css([
        'minified/bootstrap.min.css',
        'minified/kaw.min.css',
    ]);
    ?>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-sm"></div>
            <div class="col-sm"><?= $this->Flash->render() ?></div>
            <div class="col-sm"></div>
        </div>
        <br>
        <h1 class="text-center">Bem-vindo KeyAnyWhere!</h1>
        <p class="text-center">
            Neste passo a passo vamos executar a configuração inicial da ferramenta.
        </p>
        <br>
        <h2 class="text-center titulo">2- Autenticação de dois fatores (2FA)</h2>
        <div class="row justify-content-center">
            <p class="text-center">
                A utilização do 2FA é obrigatória, por isso instale um aplicativo que execute essa função. 
                Recomendamos o <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" class="text-decoration-none" target="_blank">Google Authenticator</a>.
            </p>
            <p class="text-center">
                Para ativar o 2FA do usuário <strong><?=h($user->username)?> (<?=h($user->email)?>)</strong>, escaneie o QR Code com o aplicativo authenticator.
            </p>
        </div>
        <div class="row justify-content-center">
            <?= $strSvgQrCode ?>
        </div>
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
        <?= $this->Html->script('minified/bootstrap.min.js'); ?>
</body>
</html>
