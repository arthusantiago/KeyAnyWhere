<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Usuario $usuario
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Usuario'), ['action' => 'edit', $usuario->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Usuario'), ['action' => 'delete', $usuario->id], ['confirm' => __('Are you sure you want to delete # {0}?', $usuario->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Usuario'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Usuario'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="usuario view content">
            <h3><?= h($usuario->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Username') ?></th>
                    <td><?= h($usuario->username) ?></td>
                </tr>
                <tr>
                    <th><?= __('Email') ?></th>
                    <td><?= h($usuario->email) ?></td>
                </tr>
                <tr>
                    <th><?= __('Password') ?></th>
                    <td><?= h($usuario->password) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($usuario->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($usuario->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($usuario->modified) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Entrada') ?></h4>
                <?php if (!empty($usuario->entrada)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Titulo') ?></th>
                            <th><?= __('User') ?></th>
                            <th><?= __('Password') ?></th>
                            <th><?= __('Link') ?></th>
                            <th><?= __('Anotacoes') ?></th>
                            <th><?= __('Categoria Id') ?></th>
                            <th><?= __('Subcategoria Id') ?></th>
                            <th><?= __('Usuario Id') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($usuario->entrada as $entrada) : ?>
                        <tr>
                            <td><?= h($entrada->id) ?></td>
                            <td><?= h($entrada->titulo) ?></td>
                            <td><?= h($entrada->user) ?></td>
                            <td><?= h($entrada->password) ?></td>
                            <td><?= h($entrada->link) ?></td>
                            <td><?= h($entrada->anotacoes) ?></td>
                            <td><?= h($entrada->categoria_id) ?></td>
                            <td><?= h($entrada->subcategoria_id) ?></td>
                            <td><?= h($entrada->usuario_id) ?></td>
                            <td><?= h($entrada->created) ?></td>
                            <td><?= h($entrada->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Entrada', 'action' => 'view', $entrada->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Entrada', 'action' => 'edit', $entrada->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'Entrada', 'action' => 'delete', $entrada->id], ['confirm' => __('Are you sure you want to delete # {0}?', $entrada->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
