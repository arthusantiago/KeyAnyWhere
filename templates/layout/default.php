<!doctype html>
<html lang="pt-br">
<head>
	<title>KeyAnyWhere</title>
	<?= $this->Html->charset() ?>
	<?= $this->Html->meta('icon', 'favicon.ico') ?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="sessionTimeout" id="sessionTimeout" content="<?= $sessionTimeout ?>">
	<?=$this->Html->meta('csrfToken', $csrfToken);?>
	<?php
	echo $this->Html->css([
		'minified/bootstrap.min.css',
		'minified/bootstrap-icons.min.css',
		'minified/kaw.min.css'
	]);
	echo $this->fetch('css');
	echo $this->fetch('script-first-load');
	?>
</head>
<body>
	<div id="div-lateral">
		<div id="logo-kaw">
			<?= $this->Html->image("logo-kaw.png", ['url' => ['controller' => 'Pages', 'action' => 'home']]); ?>
		</div>
		<nav id="menu-lateral">
			<?= $this->cell('CategoriasMenu::desktop') ?>
		</nav>
	</div>
	<div class="container-fluid">
		<div id="menu-superior">
			<div class="row align-items-center" id="row-menu-superior">
				<div class="col-sm"></div>
				<div class="col-sm-3" id="col-btn-busca">
					<input type="search" class="form-control input-busca" id="buscaEntrada" placeholder="Buscar uma entrada"
						data-busca-inserir-resultado="ul-busca-geral" data-busca-url="<?= $this->Url->build(['controller' => 'Entradas', 'action' => 'busca'], ['fullBase' => true]) ?>"
						data-busca-config='{"qtdCaracMin": 3}'>
					<div class="div-resultado-busca">
						<ul class="ul-busca" id="ul-busca-geral"></ul>
					</div>
				</div>
				<div class="col-sm">
					<div id="logo-responsivo">
						<?= $this->Html->image("../favicon.ico", ['url' => ['controller' => 'Pages', 'action' => 'home']]); ?>
					</div>
					<div class="text-end" id="div-btn-opcoes-menu-superior">
						<?= $this->element('Diversos/btnOpcoes') ?>
					</div>
				</div>
			</div>
		</div>
		<div id="corpo-conteudo">
			<div class="row">
				<div class="col-sm"></div>
				<div class="col-sm"><?= $this->Flash->render() ?></div>
				<div class="col-sm"></div>
			</div>
			<?= $this->fetch('content') ?>
		</div>
		<?= $this->element('Diversos/btnFlutuante') ?>
		<br><br><br>
	</div>
	<?php
		echo $this->element('Users/sessaoExpirada');
		echo $this->Html->script('minified/popper.min.js');
		echo $this->Html->script('minified/bootstrap.min.js');
		echo $this->Html->script('minified/easytimer.min.js');
		echo $this->Html->script('minified/buscaConteudo.min.js');
		echo $this->Html->script('minified/ferramentas.min.js');
		echo $this->fetch('script-last-load');
	?>
</body>
</html>