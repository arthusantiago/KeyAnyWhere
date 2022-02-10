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
            <?= $this->Html->link(__('Edit Subcategorium'), ['action' => 'edit', $subcategorium->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Subcategorium'), ['action' => 'delete', $subcategorium->id], ['confirm' => __('Are you sure you want to delete # {0}?', $subcategorium->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Subcategoria'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Subcategorium'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="subcategoria view content">
            <h3><?= h($subcategorium->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Nome') ?></th>
                    <td><?= h($subcategorium->nome) ?></td>
                </tr>
                <tr>
                    <th><?= __('Categorium') ?></th>
                    <td><?= $subcategorium->has('categorium') ? $this->Html->link($subcategorium->categorium->id, ['controller' => 'Categoria', 'action' => 'view', $subcategorium->categorium->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($subcategorium->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($subcategorium->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($subcategorium->modified) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
