<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="btn-group dropup" id="botao-flutuante">
    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        Categorias
    </button>
    <ul class="dropdown-menu">
        <?= $this->cell('CategoriasMenu::responsivo') ?>
    </ul>
</div>