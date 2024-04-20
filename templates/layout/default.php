<!doctype html>
<html lang="pt-br">
  <head>
  	<title>KeyAnyWhere</title>
	<?= $this->Html->charset() ?>
	<?= $this->Html->meta('icon', 'favicon.ico') ?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="sessionTimeout" id="sessionTimeout" content="<?=$sessionTimeout?>">
	<?=$this->Html->meta('csrfToken', $this->request->getAttribute('csrfToken'));?>
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
				<?=$this->Html->image("logo-kaw.png", ['url' => ['controller' => 'Pages', 'action' => 'home']]);?>
			</div>
			<nav id="menu-lateral">
				<?=$this->cell('CategoriasMenu')?>
			</nav>
		</div>
		<div id="menu-superior">
			<div class="row" id="opcoes-menu-superior">
				<div class="col-sm-auto">
					<a class="btn btn-outline-light botoes" role="button" href="<?=$this->Url->build(['controller' => 'Categorias', 'action' => 'index']);?>" title="Listagem categorias">
						<i class="bi bi-list-ol icone-opcao"></i>Categorias
					</a>
				</div>
				<div class="col-sm-auto ">
					<a class="btn btn-outline-light botoes" role="button" href="<?=$this->Url->build(['controller' => 'Entradas', 'action' => 'add']);?>" title="Nova entrada">
						<i class="bi bi-plus-lg icone-opcao"></i>Entrada
					</a>
				</div>
				<div class="col-sm text-center text-white" id="timerSessao"></div>
				<div class="col-sm-auto ms-auto">
					<input type="search" class="form-control input-busca" id="buscaEntrada" placeholder="Mínimo 3 caracteres"
						data-busca-inserir-resultado="ul-busca-geral" data-busca-url="<?=$this->Url->build(['controller'=>'Entradas', 'action'=>'busca'],['fullBase'=>true])?>"
						data-busca-config='{"qtdCaracMin": 3}'>
					<div class="div-resultado-busca">
						<ul class="ul-busca" id="ul-busca-geral"></ul>
					</div>
				</div>
				<div class="col-sm-auto">
					<?=$this->element('Diversos/btnOpcoes')?>
				</div>
			</div>
		</div>
		<div id="corpo-conteudo">
			<div class="row">
				<div class="col-sm"></div>
				<div class="col-sm"><?= $this->Flash->render()?></div>
				<div class="col-sm"></div>
			</div>
      		<?= $this->fetch('content') ?>
		</div>

		<div class="modal fade" id="sessaoExpiradaModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">A sessão expirou</h5>
					</div>
					<div class="modal-body">
						<p>O seu tempo de sessão acabou. Você precisa logar novamente.</p>
					</div>
					<div class="modal-footer">
						<a class="btn btn-primary" href="<?=$this->Url->build(['controller' => 'users', 'action' => 'login'])?>" role="button">Entrar</a>
					</div>
				</div>
			</div>
		</div>
		<?php
			echo $this->Html->script('minified/popper.min.js');
			echo $this->Html->script('minified/bootstrap.min.js');
			echo $this->Html->script('minified/easytimer.min.js');
			echo $this->Html->script('buscaConteudo.js');
			echo $this->Html->script('ferramentas.js');
			echo $this->fetch('script-last-load');
		?>
  	</body>
</html>