<!doctype html>
<html lang="pt-br">
  <head>
    <title>KeyAnyWhere</title>
    <?=$this->Html->charset()?>
    <?=$this->Html->meta('icon', 'favicon.ico')?>
    <?=$this->Html->meta('csrfToken', $this->request->getAttribute('csrfToken'));?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">
    <?php
      echo $this->Html->css([
      	'bootstrap/bootstrap.min.css',
      	'css-estilizacao-geral',
      ]);
      echo $this->fetch('css');
    ?>
  </head>
  <body>
    <div class="container">
      <nav class="navbar navbar-light" id="menu-administrativo">
        <div class="container">
          <div class="col-sm-4">
            <a class="navbar-brand" href="<?=$this->Url->build(['controller' => 'Pages', 'action' => 'home']);?>">
              <?=$this->Html->image("logo-kaw.png");?>
            </a>
          </div>
          <div class="col-sm-auto">
            <?=$this->element('Diversos/btnConfiguracoes')?>
          </div>
        </div>
      </nav>
      <div class="row">
        <div class="col-sm"></div>
        <div class="col-sm"><?= $this->Flash->render()?></div>
        <div class="col-sm"></div>
      </div>
      <br>
     	<?= $this->fetch('content') ?>
    </div>
    <?=$this->Html->script('bootstrap/popper.min.js')?>
		<?=$this->Html->script('bootstrap/bootstrap.js')?>
		<?=$this->Html->script('buscaConteudo.js')?>
  </body>
</html>
