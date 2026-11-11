<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $csrfToken
 */
?>
<!doctype html>
<html lang="pt-br">
    <head>
        <title><?=__('Configuração inicial')?></title>
        <?= $this->Html->charset() ?>
        <?= $this->Html->meta('icon', 'favicon.ico') ?>
        <?= $this->Html->meta('csrfToken', $csrfToken); ?>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php
        echo $this->Html->css('bootstrap-icons.min');
        echo $this->Html->css('bootstrap.min');
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
            <div class="row">
                <div class="col-sm mb-3 mt-4">
                    <h1 class="text-center"><?=__('Bem-vindo KeyAnyWhere!')?></h1>
                </div>
            </div>
            <div class="row">
                <div class="col-sm mb-3">
                    <p class="text-center"><?=__('Neste tutorial de duas etapas vamos realizar a configuração inicial da ferramenta.')?></p>
                </div>
            </div>
            <?= $this->fetch('content') ?>
            <br><br><br>
        </div>
        <?php
            echo $this->Html->script('popper.min');
			echo $this->Html->script('bootstrap.min');
			echo $this->Html->script('ferramentas.js');
			echo $this->fetch('script-last-load');
		?>
    </body>
</html>
