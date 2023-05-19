<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Log> $logs
 */
?>
<div class="logs index content">
    <?= $this->Html->link(__('New Log'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Logs') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('evento') ?></th>
                    <th><?= $this->Paginator->sort('nivel_severidade') ?></th>
                    <th><?= $this->Paginator->sort('recurso') ?></th>
                    <th><?= $this->Paginator->sort('ip_origem') ?></th>
                    <th><?= $this->Paginator->sort('usuario') ?></th>
                    <th><?= $this->Paginator->sort('mensagem') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= $this->Number->format($log->id) ?></td>
                    <td><?= h($log->evento) ?></td>
                    <td><?= $this->Number->format($log->nivel_severidade) ?></td>
                    <td><?= h($log->recurso) ?></td>
                    <td><?= h($log->ip_origem) ?></td>
                    <td><?= h($log->usuario) ?></td>
                    <td><?= h($log->mensagem) ?></td>
                    <td><?= h($log->created) ?></td>
                    <td><?= h($log->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $log->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $log->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $log->id], ['confirm' => __('Are you sure you want to delete # {0}?', $log->id)]) ?>
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
