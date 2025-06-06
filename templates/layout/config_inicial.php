<!doctype html>
<html lang="pt-br">
    <head>
        <title>KeyAnyWhere - Configuração inicial</title>
        <?= $this->Html->charset() ?>
        <?= $this->Html->meta('icon', 'favicon.ico') ?>
        <?= $this->Html->meta('csrfToken', $csrfToken); ?>
        <meta name="viewport" content="width=device-width, initial-scale=1">
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
            <div class="row">
                <div class="col-sm"></div>
                <div class="col-sm"><?=$this->Flash->render()?></div>
                <div class="col-sm"></div>
            </div>
            <?= $this->fetch('content') ?>
        </div>
        <?php
            echo $this->Html->script('minified/popper.min.js', ['checarAmbiente' => false]);
			echo $this->Html->script('minified/bootstrap.min.js', ['checarAmbiente' => false]);
			echo $this->Html->script('ferramentas.js');
			echo $this->fetch('script-last-load');
		?>
    </body>
</html>
