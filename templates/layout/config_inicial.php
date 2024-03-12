<!doctype html>
<html lang="pt-br">
    <head>
        <title>KAW - Configuração inicial</title>
        <?= $this->Html->charset() ?>
        <?= $this->Html->meta('icon', 'favicon.ico') ?>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= $this->Html->meta('csrfToken', $this->request->getAttribute('csrfToken')); ?>
        <?php
        echo $this->Html->css([
            'bootstrap/bootstrap.min.css',
            'bootstrap/bootstrap-icons.min.css',
            'css-estilizacao-geral',
        ]);
        echo $this->fetch('css');
        echo $this->fetch('script-first-load');
        ?>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-sm"></div>
                <div class="col-sm"><?= $this->Flash->render() ?></div>
                <div class="col-sm"></div>
            </div>
            <?= $this->fetch('content') ?>
        </div>
        <?php
			echo $this->Html->script('bootstrap/bootstrap.js');
			echo $this->Html->script('ferramentas.js');
			echo $this->fetch('script-last-load');
		?>
    </body>
</html>
