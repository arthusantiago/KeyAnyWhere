<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\EventoSeguranca> $eventos
 * @var \App\Model\Entity\User $userLogado
 */
?>
<div class="row">
    <div class="col-sm-auto">
        <?= $this->element(
            'breadcrumb',
            ['caminho' => [
                ['Pages', 'home', 'Home'],
                'Eventos de segurança'
            ]]
        ); ?>
    </div>
</div>

<div class="row">
    <div class="col-sm mb-3">
        <span class="titulo"><?= __('Eventos de segurança') ?></span>
        <p class="text-body-secondary mb-0">
            <?= $userLogado->root
                ? __('Tentativas de login com erro, acessos negados e conteúdo malicioso bloqueado, de todos os usuários.')
                : __('Tentativas de login com erro na sua conta, acessos negados e conteúdo malicioso bloqueado.') ?>
        </p>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-borderless table-striped table-hover">
        <thead>
            <tr class="text-center titulo-coluna-tabela">
                <th><?= __('Data') ?></th>
                <th><?= __('Evento') ?></th>
                <?php if ($userLogado->root): ?>
                    <th><?= __('Usuário') ?></th>
                    <th><?= __('IP de origem') ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($eventos as $evento): ?>
                <tr class="text-center">
                    <td><?= h($evento->created) ?></td>
                    <td><span class="badge <?= h($evento->classeNivel()) ?>"><?= h($evento->titulo()) ?></span></td>
                    <?php if ($userLogado->root): ?>
                        <td><?= h($evento->user?->username ?? ($evento->usuario_id ? __('Usuário excluído (#{0})', $evento->usuario_id) : '—')) ?></td>
                        <td><?= h($evento->ip ?? '—') ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php if ((int)$this->Paginator->param('totalCount') === 0): ?>
    <p class="text-center"><?= __('Nenhum evento de segurança registrado.') ?></p>
<?php endif; ?>
<br>
<?= $this->element('paginacao'); ?>
