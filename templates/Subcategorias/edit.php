<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Subcategorium $subcategorium
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $subcategorium->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $subcategorium->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Subcategoria'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="subcategoria form content">
            <?= $this->Form->create($subcategorium) ?>
            <fieldset>
                <legend><?= __('Edit Subcategorium') ?></legend>
                <?php
                    echo $this->Form->control('nome');
                    echo $this->Form->control('categoria_id', ['options' => $categoria]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
