<!doctype html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?=$this->Html->css([
    	'bootstrap/bootstrap.min.css',
    	'login'
    ]);?>
    <title>KAW</title>
  </head>
  <body>
	<div class="container">
		<div class="row">
          <div class="col-sm"></div>
          <div class="col-sm"><?= $this->Flash->render()?></div>
          <div class="col-sm"></div>
        </div>
        <?= $this->Form->create(null,['url' => ['controller' => 'users', 'action' => 'login']]) ?>
          <?php $this->Form->secure([
              'email',
              'password',
              '2fa',
          ]); ?>
            <div id="div-login-externa">
                <div id="div-login-interna">
                    <div id="div-logo" class="mb-4">
                        <?= $this->Html->image('logo-kaw.png', ['id' => 'login-logo-kaw'])?>
                    </div>
                    <div class="mb-3 elementoInterno">
                        <label for="email" class="form-label text-light">Usuário</label>
                        <input type="text" class="form-control " id="email" name="email" placeholder="seu@e-mail.com" maxlength="100" required>
                    </div>
                    <div class="mb-3 elementoInterno">
                        <label for="password" class="form-label text-light">Senha</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="******" minlength="12" required>
                    </div>
                    <div class="mb-3 elementoInterno">
                        <label for="2fa" class="form-label text-light" title="Two-Factor Authentication (2FA)">Segundo fator de autenticação</label>
                        <input type="text" class="form-control" id="2fa" name="2fa" minlength="6" title="Two-Factor Authentication (2FA)" required >
                    </div>
                    <div class="mb-3 elementoInterno">
                        <a href="#">Esqueci minha senha</a>
                    </div>
                    <div class="mb-3 text-end elementoInterno">
                      <button class="btn btn-primary" type="submit">Entrar</button>
                    </div>
                </div>
            </div>
        <?= $this->Form->end(['data-type' => 'hidden']); ?>
    </div>
    <?=$this->Html->script('bootstrap/bootstrap.min.js');?>
  </body>
</html>
