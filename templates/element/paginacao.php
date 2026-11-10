<?php
/**
 * @var \App\View\AppView $this
 */
?>
<nav>
    <ul class="pagination justify-content-center">
        <?=$this->Paginator->first(__('Primeira'))?>
        <?=$this->Paginator->numbers(['modulus' => 4])?>
        <?=$this->Paginator->last(__('Última'))?>
    </ul>
    <p class="text-center"><?=__('Exibindo {0} registros de {1}', [$this->Paginator->param('count'), $this->Paginator->param('totalCount')])?></p>
</nav>
