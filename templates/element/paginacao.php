<nav>
    <ul class="pagination justify-content-center">
        <?= $this->Paginator->first('<< ' . __('Primeira')) ?>
        <?= $this->Paginator->prev(__('Anterior')) ?>
        <?= $this->Paginator->numbers() ?>
        <?= $this->Paginator->next(__('Próximo')) ?>
        <?= $this->Paginator->last(__('Última') . ' >>') ?>
    </ul>
    <p class="text-center"><?= $this->Paginator->counter(__('Exibindo {{current}} registros de {{count}}')) ?></p>
</nav>
