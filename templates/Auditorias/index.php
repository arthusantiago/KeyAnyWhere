<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Auditoria[]|\Cake\Collection\CollectionInterface $auditorias
 */
?>
<div class="row">
    <div class="col-sm-auto">
        <?= $this->element(
            'breadcrumb',
            ['caminho' => [
                ['Pages', 'home', 'Home'],
                'Configurações',
                'Auditoria'
            ]]
        ); ?>
    </div>
</div>

<div class="row">
    <div class="col-sm mb-3">
        <span class="titulo"><?= __('Auditoria') ?></span>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-borderless table-striped table-hover">
        <thead>
            <tr class="text-center titulo-coluna-tabela">
                <th><?= $this->Paginator->sort('contexto', __('Contexto')) ?></th>
                <th><?= $this->Paginator->sort('acao', __('Ação')) ?></th>
                <th><?= __('Usuário') ?></th>
                <th><?= $this->Paginator->sort('created', __('Data')) ?></th>
                <th><?= __('Ações') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($auditorias as $auditoria): ?>
                <tr class="text-center">
                    <td><?= h(ucfirst($auditoria->contexto)) ?></td>
                    <td><?= h(ucfirst($auditoria->acao)) ?></td>
                    <td><?= h($auditoria->descricaoUsuario()) ?></td>
                    <td><?= h($auditoria->created) ?></td>
                    <td>
                        <div class="btn-group btn-group-sm botoes" role="group">
                            <a class="btn btn-sm btn-outline-secondary" role="button" href="<?= $this->Url->build(['controller' => 'Auditorias', 'action' => 'view', $auditoria->id]) ?>">
                                <i class="bi bi-eye-fill"></i><?= __('Detalhes') ?>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<br>
<?= $this->element('paginacao'); ?>
