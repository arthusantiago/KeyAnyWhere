<!doctype html>
<html lang="pt-br">
  <head>
  	<title>KeyAnyWhere</title>
	<?= $this->Html->charset() ?>
	<?= $this->Html->meta('icon', 'favicon.ico') ?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?=$this->Html->meta('csrfToken', $this->request->getAttribute('csrfToken'));?>
	<link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">
    <?php
      echo $this->Html->css([
      	'bootstrap/bootstrap.min.css',
      	'bootstrap/bootstrap-icons.css',
      	'css-estilizacao-geral',
      ]);
      echo $this->fetch('css');
    ?>
	<?=$this->Html->script('categorias.js');?>
	<?=$this->Html->script('ferramentas.js');?>
	<?=$this->Html->script('geradorSenha.js');?>
	<script src="https://kit.fontawesome.com/6be704c138.js" crossorigin="anonymous"></script>
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
					<a class="btn btn-outline-light botoes" role="button" href="<?=$this->Url->build(['controller' => 'Categorias', 'action' => 'index']);?>">
						<i class="bi bi-list-ol icone-opcao"></i>Categorias
					</a>
				</div>
				<div class="col-sm-auto ">
					<a class="btn btn-outline-light botoes" role="button" href="<?=$this->Url->build(['controller' => 'Entradas', 'action' => 'add']);?>">
						<i class="bi bi-key icone-opcao"></i>Nova Entrada
					</a>
				</div>
				<div class="col-sm-auto ms-auto">
					<input type="search" class="form-control input-busca" id="buscaEntrada"	placeholder="Mínimo 3 caracteres"
						onblur="removeResultadoBuscaGenerico('buscaEntrada', 'ul-busca')"
						oninput="buscaGenerica(
							'buscaEntrada',
							'ul-busca',
							'<?=$this->Url->build(['controller' => 'Entradas', 'action' => 'busca'], ['fullBase' => true])?>',
							{qtdCaracMin:'3'}
						)"
					>
					<div class="div-resultado-busca">
						<ul class="ul-busca" id="ul-busca"></ul>
					</div>
				</div>
				<div class="col-sm-auto">
					<?=$this->element('Diversos/btnConfiguracoes')?>
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
		<?=$this->Html->script('bootstrap/popper.min.js')?>
		<?=$this->Html->script('bootstrap/bootstrap.js')?>
		<?=$this->Html->script('buscaConteudo.js')?>
  	</body>
</html>