<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Subcategorium[]|\Cake\Collection\CollectionInterface $subcategoria
 */
?>
<div class="subcategoria index content">
    <?= $this->Html->link(__('New Subcategorium'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Subcategoria') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('nome') ?></th>
                    <th><?= $this->Paginator->sort('categoria_id') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subcategoria as $subcategorium): ?>
                <tr>
                    <td><?= $this->Number->format($subcategorium->id) ?></td>
                    <td><?= h($subcategorium->nome) ?></td>
                    <td><?= $subcategorium->has('categorium') ? $this->Html->link($subcategorium->categorium->id, ['controller' => 'Categoria', 'action' => 'view', $subcategorium->categorium->id]) : '' ?></td>
                    <td><?= h($subcategorium->created) ?></td>
                    <td><?= h($subcategorium->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $subcategorium->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $subcategorium->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $subcategorium->id], ['confirm' => __('Are you sure you want to delete # {0}?', $subcategorium->id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
