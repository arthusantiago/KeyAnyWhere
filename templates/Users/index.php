<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Usuario[]|\Cake\Collection\CollectionInterface $usuario
 */
?>

<div class="row">
    <div class="col-sm">
        <span class="titulo">Usuários</span>
    </div>
    <div class="col-sm text-end">
        <?= $this->element('Diversos/btnNovo', ['parametros' => ['controller' => 'Users', 'texto' => 'Usuário']])?>
    </div>
</div>

<table class="table table-borderless table-striped table-hover">
    <thead>
        <tr class="text-center titulo-coluna-tabela">
            <th><?= $this->Paginator->sort('username') ?></th>
            <th><?= $this->Paginator->sort('email', 'E-mail') ?></th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr class="text-center">
                <td><?= h($user->username)?></td>
                <td><?= h($user->email)?></td>
                <td>
                    <?=$this->element('Diversos/btnEditar', ['parametros' => ['controller' => 'Users', 'id' => $user->id]])?>
                    <?= $this->element('Diversos/btnExcluir', ['parametros' => ['controller' => 'Users', 'id' => $user->id]])?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<br>
<?= $this->element('paginacao');?>
