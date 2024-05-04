<div class="row">
    <div class="col-sm-auto">
        <span class="titulo">Categoria <?=h($categoria->nomeDescrip())?></span>
    </div>
</div>

<br>

<?= $this->Form->create($categoria); ?>
	<?php $this->Form->secure(['nome']); ?>
	<div class="row">
		<div class="col-sm-4">
		  <label for="nome">Nome da categoria</label>
		  <input type="text" class="form-control inputs" id="nome" name="nome" value="<?=h($categoria->nomeDescrip())?>" maxlength="88">
		</div>
	</div>
	<div class="row">
	    <div class="col-sm text-end">
	        <?= $this->element('Diversos/btnSalvar')?>
	    </div>
	</div>
<?= $this->Form->end(['data-type' => 'hidden']);?>