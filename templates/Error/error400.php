<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Database\StatementInterface $error
 * @var string $message
 * @var string $url
 */
use App\Application;
use Cake\Error\Debugger;

$this->assign('title', $message);

if (Application::isTheExecutionEnvironment(Application::DESENVOLVIMENTO)) :
    $this->layout = 'dev_error';
    $this->assign('templateName', 'error400.php');
    $this->start('file');
?>
    <?php if (!empty($error->queryString)) : ?>
        <p class="notice">
            <strong>SQL Query: </strong>
            <?= h($error->queryString) ?>
        </p>
    <?php endif; ?>
    <?php if (!empty($error->params)) : ?>
        <strong>SQL Query Params: </strong>
        <?php Debugger::dump($error->params) ?>
    <?php endif; ?>
    <?php
        echo $this->element('auto_table_warning');
        $this->end();
    endif;
?>

<h2><?= __('Erro ao acessar a URL') ?></h2>
<p>
    <?=__(h($message))?> <strong><?=h($url)?></strong>
</p>
