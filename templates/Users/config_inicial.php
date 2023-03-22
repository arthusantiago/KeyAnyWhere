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
        'bootstrap/bootstrap.min.css',
        'css-estilizacao-geral',
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
        <?php if (empty($strSvgQrCode)) : ?>
            <br>
            <h2 class="text-center titulo">1- Usuário de acesso</h2>
            <p class="text-center">Vamos criar o primeiro usuário do sistema, que também será o usuário master.</p>
            <?= $this->Form->create(null, ['url' => ['controller' => 'users', 'action' => 'configInicial']]); ?>
            <?php $this->Form->secure(['username', 'email', 'password']); ?>
            <div class="row justify-content-center">
                <div class="col-sm-4">
                    <label for="username" class="form-label">Nome</label>
                    <input type="text" class="form-control inputs" id="username" name="username" autocomplete="nickname" placeholder="Fulado Tal">
                </div>
            </div>
            <br>
            <div class="row justify-content-center">
                <div class="col-sm-4">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control inputs" id="email" name="email" autocomplete="email" placeholder="seu@email.com.br">
                </div>
            </div>
            <br>
            <div class="row justify-content-center">
                <div class="col-sm-4">
                    <label for="password">Senha</label>
                    <input type="text" id="password" name="password" class="form-control inputs" autocomplete="new-password" minlength="12" placeholder="************">
                    <p>
                        <strong>Atenção:</strong> Se essa senha for hackeada todas as outras senhas estarão desprotegidas.
                        Escolha uma senha que atenda a todos os requisitos abaixo:
                    </p>
                    <ul>
                        <li>No mínimo 12 caracteres</li>
                        <li>Contendo letras, números e caracteres especiais como: !@#$%&*()_-+</li>
                        <li>Não deve ser utilizada em outro lugar</li>
                        <li>Não anote, memorize a senha</li>
                    </ul>
                </div>
            </div>
            <br>
            <div class="row justify-content-center">
                <div class="col-sm-1">
                    <?= $this->element('Diversos/btnSalvar') ?>
                </div>
            </div>
            <?= $this->Form->end(['data-type' => 'hidden']); ?>
        <?php else : ?>
            <br>
            <h2 class="text-center titulo">2- Autenticação de dois fatores (2FA)</h2>
            <div class="row justify-content-center">
                <p class="text-center">
                    A utilização do 2FA é obrigatória, por isso instale um aplicativo que execute essa função. 
                    Recomendamos o <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" class="text-decoration-none" target="_blank">Google Authenticator</a>.
                </p>
                <p class="text-center">Escanei o QR Code com o aplicativo authenticator instalado em seu celular.</p>
            </div>
            <div class="row justify-content-center">
                <?= $strSvgQrCode ?>
            </div>
            <?= $this->Form->create(null, ['url' => ['controller' => 'users', 'action' => 'configInicial']]); ?>
            <?php $this->Form->secure(['id']); ?>
                <input type="hidden" name="id" value="<?=$user->id?>"/>
                <div class="row justify-content-center">
                    <div class="col-sm-1">
                        <?= $this->element('Diversos/btnSalvar', ['parametros' => ['texto' => 'Finalizar']]) ?>
                    </div>
                </div>
            <?= $this->Form->end(['data-type' => 'hidden']); ?>
        <?php endif; ?>

        <?= $this->Html->script('bootstrap/bootstrap.min.js'); ?>
</body>
</html>
