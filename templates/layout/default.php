<!doctype html>
<html lang="pt-br">
  <head>
	<?= $this->Html->charset() ?>
	<?= $this->Html->meta('icon', 'favicon.ico') ?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">
    <title>KAW</title>
    <?php 
      echo $this->Html->css([
      	'bootstrap/bootstrap.css',
      	'bootstrap/bootstrap-icons.css',
      	'css-estilizacao-geral',
      ]);
      echo $this->fetch('css');
    ?>
  </head>
  <body onload="categoriaSelecionada()">
		<div id="divLateral">
			<div id="logoKAW">
				<?=$this->Html->image("logo-kaw.png", ['url' => ['controller' => 'Pages', 'action' => 'display']]);?>
			</div>
			<nav id="menuLateral">
				<?=$this->cell('CategoriasMenu')?>
			</nav>
		</div>
		<div id="menuSuperior">

<div class="row" style="padding-top: 5px;">
		<div class="col-sm-auto ">
			<a class="dropdown-item" href="<?=$this->Url->build(['controller' => 'Entradas', 'action' => 'add']);?>" title="Nova Entrada">
				<i class="bi bi-key icone-interno-btn-opcoes"></i>Entrada
			</a>	
		</div>
		<div class="col-sm-auto">
			<a class="dropdown-item" href="<?=$this->Url->build(['controller' => 'Categorias', 'action' => 'add']);?>" title="Nova Categoria">
				<i class="bi bi-list-ol icone-interno-btn-opcoes"></i>Categoria
			</a>
		</div>
		<div class="col-sm-auto">
			<a class="dropdown-item" href="<?=$this->Url->build(['controller' => 'Subcategorias', 'action' => 'add']);?>" title="Nova Subcategoria">
				<i class="bi bi-list-nested icone-interno-btn-opcoes"></i>Subcategoria
			</a>
		</div>
		<div class="col-sm-auto">
			<input type="search" class="form-control" name="">
		</div>
		<div class="col-sm">
			<div class="btn-group dropup">
				<button type="button" class="btn btn-lg navbar-toggler" data-bs-toggle="dropdown">
					<span class="navbar-toggler-icon"></span>
				</button>

				<ul class="dropdown-menu">
					<li>
						<button class="dropdown-item" type="button" onclick="">
							<i class="bi bi-search icone-interno-btn-opcoes"></i>Busca
						</button>
					</li>
					<li>
						<a class="dropdown-item" href="<?=$this->Url->build(['controller' => 'Entradas', 'action' => 'add']);?>" title="Nova Entrada">
							<i class="bi bi-key icone-interno-btn-opcoes"></i>Entrada
						</a>
					</li>
					<li>
						<a class="dropdown-item" href="<?=$this->Url->build(['controller' => 'Categorias', 'action' => 'add']);?>" title="Nova Categoria">
							<i class="bi bi-list-ol icone-interno-btn-opcoes"></i>Categoria
						</a>
					</li>
					<li>
						<a class="dropdown-item" href="<?=$this->Url->build(['controller' => 'Subcategorias', 'action' => 'add']);?>" title="Nova Subcategoria">
							<i class="bi bi-list-nested icone-interno-btn-opcoes"></i>Subcategoria
						</a>
					</li>
				</ul>
			</div>
		</div>
</div>


		</div>
		<div id="corpoConteudo">
      <?= $this->fetch('content') ?>
		</div>
		<div class="btn-group dropup" id="menuSuspenso">
			<button type="button" class="btn btn-lg" data-bs-toggle="dropdown">
				<i class="bi bi-three-dots-vertical" id="icone-btn-opcoes"></i>
			</button>
			<ul class="dropdown-menu">
				<li>
					<button class="dropdown-item" type="button" onclick="">
						<i class="bi bi-search icone-interno-btn-opcoes"></i>Busca
					</button>
				</li>
				<li>
					<a class="dropdown-item" href="<?=$this->Url->build(['controller' => 'Entradas', 'action' => 'add']);?>" title="Nova Entrada">
						<i class="bi bi-key icone-interno-btn-opcoes"></i>Entrada
					</a>
				</li>
				<li>
					<a class="dropdown-item" href="<?=$this->Url->build(['controller' => 'Categorias', 'action' => 'add']);?>" title="Nova Categoria">
						<i class="bi bi-list-ol icone-interno-btn-opcoes"></i>Categoria
					</a>
				</li>
				<li>
					<a class="dropdown-item" href="<?=$this->Url->build(['controller' => 'Subcategorias', 'action' => 'add']);?>" title="Nova Subcategoria">
						<i class="bi bi-list-nested icone-interno-btn-opcoes"></i>Subcategoria
					</a>
				</li>
			</ul>
		</div>

		<?=$this->Html->script('bootstrap/popper.min.js')?>
	  <?=$this->Html->script('bootstrap/bootstrap.js')?>
	  <?=$this->Html->script('buscaConteudo.js')?>
	  <?=$this->Html->script('clipboard.min.js');?>
	  <?=$this->Html->script('categorias.js');?>
	  <!-- Iniciando o Objeto responsável por gerenciar o clipboard -->
	  <script type="text/javascript">
	    var clipboardBtnSimples = new ClipboardJS('.btn-sm');
	  </script>
  </body>
</html>