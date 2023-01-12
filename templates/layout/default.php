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
	<?=$this->Html->script('categorias.js');?>
	<?=$this->Html->script('ferramentas.js');?>
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
				<div class="col-sm-auto ">
					<a class="btn btn-outline-light botoes" role="button" href="<?=$this->Url->build(['controller' => 'Entradas', 'action' => 'add']);?>">
						<i class="bi bi-key icone-opcao"></i>Nova Entrada
					</a>
				</div>
				<div class="col-sm-auto">
					<a class="btn btn-outline-light botoes" role="button" href="<?=$this->Url->build(['controller' => 'Categorias', 'action' => 'index']);?>">
						<i class="bi bi-list-ol icone-opcao"></i>Categorias
					</a>
				</div>
				<div class="col-sm-auto ms-auto">
					<input type="search" class="form-control" name="" placeholder="Buscar entrada">
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