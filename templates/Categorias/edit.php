<div class="row">
    <div class="col-sm-auto">
        <span class="titulo">Categoria <?=$categoria->nome?></span>
    </div>
</div>

<br>

<?= $this->Form->create($categoria); ?>
	<?php $this->Form->secure(['nome']); ?>
	<div class="row">
		<div class="col-sm-4">
		  <label for="nome">Nome da categoria</label>
		  <input type="text" class="form-control inputs" id="nome" name="nome" value="<?=$categoria->nome?>">
		</div>
	</div>
	<div class="row">
	    <div class="col-sm text-end">
	        <?= $this->element('Diversos/btnSalvar')?>
	    </div>
	</div>
<?= $this->Form->end(['data-type' => 'hidden']);?>

<hr>

<div class="row">
    <div class="col-sm-auto">
        <span class="titulo">Subcategorias</span>
    </div>
</div>

<br>

<table class="table table-borderless table-striped table-hover">
    <thead>
        <tr class="text-center titulo-coluna-tabela">
            <th>Nome da subcategoria</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($subcategorias as $subcategoria): ?>
            <tr class="text-center">
                <td><?= h($subcategoria->nome) ?></td>
                <td>
                    <?=$this->element('Diversos/btnEditar', ['parametros' => ['controller' => 'Subcategorias', 'id' => $subcategoria->id]])?>
                    <?=$this->element('Diversos/btnExcluir', ['parametros' => ['controller' => 'Subcategorias', 'id' => $subcategoria->id]])?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>