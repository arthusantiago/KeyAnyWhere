<!doctype html>
<html lang="pt-br">
  <head>
    <title>KeyAnyWhere</title>
	  <?= $this->Html->charset() ?>
	  <?= $this->Html->meta('icon', 'favicon.ico') ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
      echo $this->Html->css('minified/bootstrap-icons.min.css', ['checarAmbiente' => false]);
      echo $this->Html->css('minified/bootstrap.min.css', ['checarAmbiente' => false]);
      echo $this->Html->css('geral.css');
      echo $this->Html->css('responsivo.css');
    ?>
  </head>
  <body>
	<div class="container">
		<div class="row">
      <div class="col-sm"></div>
      <div class="col-sm"><?= $this->Flash->render()?></div>
      <div class="col-sm"></div>
    </div>
    <div class="row">
      <div class="col-sm"></div>
      <div class="col-sm"><h6 class="text-white bg-danger text-center">DEMONSTRACAO</h6></div>
      <div class="col-sm"></div>
    </div>
    <?= $this->fetch('content') ?>
  </div>
    <?php
      echo $this->Html->script('minified/bootstrap.min.js', ['checarAmbiente' => false]);
      echo $this->Html->script('minified/ferramentas.min.js', ['checarAmbiente' => false]);
    ?>
  </body>
</html>
