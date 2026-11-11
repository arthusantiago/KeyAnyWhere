<?php
/**
 * Esse layout deve ser utilizado nos casos onde algo não aconteceu como deveria, como falha ou erro.
 * Ele também pode ser utilizado em situações onde o usuário não está autenticado. Exibindo informações para o 'mundo'.
 *
 * ***NÃO*** expor informações sensíveis aqui.
 *
 * @var \App\View\AppView $this
 */
?>
<!doctype html>
<html lang="pt-br">
  <head>
    <title><?=$this->fetch('title', 'Ooops');?></title>
	  <?= $this->Html->charset() ?>
	  <?= $this->Html->meta('title', 'favicon.ico') ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
      echo $this->Html->css('bootstrap.min');
      echo $this->Html->css('responsivo.css');
    ?>
  </head>
  <body>
    <div class="container">
      <div class="row text-center mb-4 mt-4">
          <div class="col-md" >
              <?=$this->Html->image('logo-kaw.png', ['url' => ['controller' => 'Pages', 'action' => 'home']]);?>
          </div>
      </div>
      <br>
      <h1><?= __('Aconteceu algum problema ao acessar o que você queria')?> 😔</h1>
      <br>
     	<?=$this->fetch('content')?>
      <br>
      <p class="text-center">
        <a href="<?= $this->Url->build(['controller' => 'Pages', 'action' => 'home']) ?>" class="btn btn-primary">
            <?= __('Voltar a página inicial') ?>
        </a>
      </p>
    </div>
    <?php
      echo $this->Html->script('bootstrap.min');
    ?>
  </body>
</html>
