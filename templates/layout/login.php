<!doctype html>
<html lang="pt-br">
  <head>
    <title>KeyAnyWhere</title>
	<?= $this->Html->charset() ?>
	<?= $this->Html->meta('icon', 'favicon.ico') ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?=$this->Html->css([
    	'bootstrap/bootstrap.min.css',
    	'kaw.min.css'
    ]);?>
  </head>
  <body>
	<div class="container">
		<div class="row">
          <div class="col-sm"></div>
          <div class="col-sm"><?= $this->Flash->render()?></div>
          <div class="col-sm"></div>
        </div>
        <?= $this->fetch('content') ?>
    </div>
    <?=$this->Html->script('minified/bootstrap.min.js');?>
  </body>
</html>
