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
            'minified/bootstrap.min.css',
            'minified/bootstrap-icons.min.css',
            'minified/kaw.min.css',
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
			echo $this->Html->script('minified/bootstrap.min.js');
			echo $this->Html->script('minified/ferramentas.min.js');
			echo $this->fetch('script-last-load');
		?>
    </body>
</html>