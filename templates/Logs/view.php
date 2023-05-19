<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Log $log
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Log'), ['action' => 'edit', $log->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Log'), ['action' => 'delete', $log->id], ['confirm' => __('Are you sure you want to delete # {0}?', $log->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Logs'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Log'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="logs view content">
            <h3><?= h($log->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Evento') ?></th>
                    <td><?= h($log->evento) ?></td>
                </tr>
                <tr>
                    <th><?= __('Recurso') ?></th>
                    <td><?= h($log->recurso) ?></td>
                </tr>
                <tr>
                    <th><?= __('Ip Origem') ?></th>
                    <td><?= h($log->ip_origem) ?></td>
                </tr>
                <tr>
                    <th><?= __('Usuario') ?></th>
                    <td><?= h($log->usuario) ?></td>
                </tr>
                <tr>
                    <th><?= __('Mensagem') ?></th>
                    <td><?= h($log->mensagem) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($log->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Nivel Severidade') ?></th>
                    <td><?= $this->Number->format($log->nivel_severidade) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($log->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($log->modified) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
