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
        'Editando'
      ]]
    ); ?>
  </div>
</div>

<div class="row">
	<div class="col-sm-auto mb-3">
		<span class="titulo">Categoria <?= h($categoria->nomeDescrip()) ?></span>
	</div>
</div>

<?= $this->Form->create($categoria); ?>
<?php $this->Form->secure(['nome']); ?>
<div class="row">
	<div class="col-sm-4 mb-3">
		<label for="nome">Nome da categoria</label>
		<input type="text" class="form-control inputs" id="nome" name="nome" value="<?= h($categoria->nomeDescrip()) ?>" maxlength="88">
	</div>
</div>
<div class="row">
	<div class="col-sm text-end">
		<?= $this->element('Diversos/btnSalvar') ?>
	</div>
</div>
<?= $this->Form->end(['data-type' => 'hidden']); ?>