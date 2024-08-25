<nav>
    <ul class="pagination justify-content-center">
        <?=$this->Paginator->first('Primeira')?>
        <?=$this->Paginator->numbers(['modulus' => 4])?>
        <?=$this->Paginator->last('Última')?>
    </ul>
    <p class="text-center"><?= $this->Paginator->counter(__('Exibindo {{current}} registros de {{count}}')) ?></p>
</nav>
