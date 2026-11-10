<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Categoria $categoria
 */
?>
<div class="row">
  <div class="col-sm-auto">
    <?= $this->element(
      'breadcrumb',
      ['caminho' => [
        ['Categorias', 'index', 'Categorias'],
        'Cadastrando'
      ]]
    ); ?>
  </div>
</div>

<div class="row">
  <div class="col-sm-auto mb-3">
    <span class="titulo"><?=__('Nova categoria')?></span>
  </div>
</div>

<?= $this->Form->create($categoria); ?>
<?php $this->Form->secure(['nome']); ?>
<div class="row">
  <div class="col-sm-4 mb-3">
    <label for="nome"><?=__('Nome da categoria')?></label>
    <input type="text" class="form-control inputs" id="nome" name="nome" maxlength="88" required>
  </div>
</div>
<div class="row">
  <div class="col-sm mb-3 text-end">
    <?= $this->element('Diversos/btnSalvar')?>
  </div>
</div>
<?= $this->Form->end(['data-type' => 'hidden']); ?>