<!doctype html>
<html lang="pt-br">
  <head>
	<?= $this->Html->charset() ?>
	<?= $this->Html->meta('icon', 'favicon.ico') ?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">
    <title>KeyAnyWhere</title>
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
		<div id="div-lateral">
			<div id="logo-kaw">
				<?=$this->Html->image("logo-kaw.png", ['url' => ['controller' => 'Pages', 'action' => 'display']]);?>
			</div>
			<nav id="menu-lateral">
				<?=$this->cell('CategoriasMenu')?>
			</nav>
		</div>
		<div id="menu-superior">
			<div class="row" id="opcoes-menu-superior">
				<div class="col-sm-auto ">
					<a class="btn btn-outline-light" role="button" href="<?=$this->Url->build(['controller' => 'Entradas', 'action' => 'add']);?>">
						<i class="bi bi-key icone-opcao"></i>Entrada
					</a>	
				</div>
				<div class="col-sm-auto">
					<a class="btn btn-outline-light" role="button" href="<?=$this->Url->build(['controller' => 'Categorias', 'action' => 'add']);?>">
						<i class="bi bi-list-ol icone-opcao"></i>Categoria
					</a>
				</div>
				<div class="col-sm-auto">
					<a class="btn btn-outline-light" role="button" href="<?=$this->Url->build(['controller' => 'Subcategorias', 'action' => 'add']);?>">
						<i class="bi bi-list-nested icone-opcao"></i>Subcategoria
					</a>
				</div>
				<div class="col-sm-auto ms-auto">
					<input type="search" class="form-control" name="">
				</div>
				<div class="col-sm-auto">
					<div class="btn-group dropup">
						<button type="button" class="btn btn-outline-light" data-bs-toggle="dropdown">
							<i class="bi bi-three-dots"></i>
						</button>
						<ul class="dropdown-menu">
							<li>
								<a class="dropdown-item" href="#">
									<i class="bi bi-gear icone-opcao"></i>Configurações
								</a>
							</li>
							<li>
								<a class="dropdown-item" href="<?=$this->Url->build(['controller' => 'Users', 'action' => 'minhaConta'])?>">
									<i class="bi bi-person icone-opcao"></i>Minha Conta
								</a>
							</li>
							<li>
								<a class="dropdown-item" href="<?=$this->Url->build(['controller' => 'Users', 'action' => 'logout'])?>">
									<i class="bi bi-box-arrow-right icone-opcao"></i>Sair
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div id="corpo-conteudo">
      <?= $this->fetch('content') ?>
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