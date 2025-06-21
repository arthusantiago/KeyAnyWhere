<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $csrfToken
 * @var string $sessionTimeout
 */
?>
<!doctype html>
<html lang="pt-br">
  <head>
    <title>KeyAnyWhere</title>
    <?=$this->Html->charset()?>
    <?=$this->Html->meta('icon', 'favicon.ico')?>
    <?=$this->Html->meta('csrfToken', $csrfToken);?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="sessionTimeout" id="sessionTimeout" content="<?=$sessionTimeout?>">
    <?php
    	echo $this->Html->css('minified/bootstrap-icons.min.css', ['checarAmbiente' => false]);
      echo $this->Html->css('minified/bootstrap.min.css', ['checarAmbiente' => false]);
      echo $this->Html->css('geral.css');
      echo $this->Html->css('responsivo.css');
      echo $this->fetch('css');
      echo $this->fetch('script-first-load');
    ?>
  </head>
  <body>
    <div class="container">
      <div id="menu-administrativo">
        <div class="row">
          <div class="col-sm" id="logo-nao-responsivo">
            <?=$this->Html->image('logo-kaw.png', ['url' => ['controller' => 'Pages', 'action' => 'home']]);?>
          </div>
          <div class="col-sm-auto my-1">
            <div id="logo-responsivo">
              <?=$this->Html->image('../favicon.ico', ['url' => ['controller' => 'Pages', 'action' => 'home']]);?>
            </div>
            <div class="text-end" id="div-btn-opcoes-menu-superior">
              <?=$this->element('Diversos/btnOpcoes')?>
            </div>
          </div>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-sm"></div>
        <div class="col-sm"><?=$this->Flash->render()?></div>
        <div class="col-sm"></div>
      </div>
      <div class="row">
				<div class="col-sm"></div>
				<div class="col-sm"><h6 class="text-white bg-danger text-center">DEMONSTRACAO</h6></div>
				<div class="col-sm"></div>
			</div>
     	<?=$this->fetch('content')?>
    </div>
    <?php
      echo $this->element('buscaModal');
      echo $this->element('Users/sessaoExpirada');
      echo $this->Html->script('minified/popper.min.js', ['checarAmbiente' => false]);
		  echo $this->Html->script('minified/bootstrap.min.js', ['checarAmbiente' => false]);
		  echo $this->Html->script('minified/easytimer.min.js', ['checarAmbiente' => false]);
		  echo $this->Html->script('ferramentas.js');
		  echo $this->Html->script('buscaConteudo.js');
      echo $this->fetch('script-last-load');
    ?>
  </body>
</html>
