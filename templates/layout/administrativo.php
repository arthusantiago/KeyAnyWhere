<!doctype html>
<html lang="pt-br">
  <head>
    <title>KeyAnyWhere</title>
    <?=$this->Html->charset()?>
    <?=$this->Html->meta('icon', 'favicon.ico')?>
    <?=$this->Html->meta('csrfToken', $this->request->getAttribute('csrfToken'));?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="sessionTimeout" id="sessionTimeout" content="<?=$sessionTimeout?>">
    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">
    <?php
      echo $this->Html->css([
      	'bootstrap/bootstrap.min.css',
      	'css-estilizacao-geral',
      ]);
      echo $this->fetch('css');
    ?>
    <script src="https://kit.fontawesome.com/6be704c138.js" crossorigin="anonymous"></script>
  </head>
  <body>
    <div class="container">
      <nav class="navbar navbar-light" id="menu-administrativo">
        <div class="container">
          <div class="col-sm-2">
            <a class="navbar-brand" href="<?=$this->Url->build(['controller' => 'Pages', 'action' => 'home']);?>">
              <?=$this->Html->image("logo-kaw.png");?>
            </a>
          </div>
          <div class="col-sm text-center text-white" id="timerSessao"></div>
          <div class="col-sm-auto">
            <?=$this->element('Diversos/btnOpcoes')?>
          </div>
        </div>
      </nav>
      <div class="row">
        <div class="col-sm"></div>
        <div class="col-sm"><?= $this->Flash->render()?></div>
        <div class="col-sm"></div>
      </div>
      <br>
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
    <?=$this->Html->script('bootstrap/popper.min.js')?>
		<?=$this->Html->script('bootstrap/bootstrap.js')?>
    <?=$this->Html->script('easytimer.min.js');?>
		<?=$this->Html->script('buscaConteudo.js')?>
    <?=$this->Html->script('ferramentas.js');?>
  </body>
</html>
