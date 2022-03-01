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
    <div class="col-sm text-end">
        <button type="button" class="btn btn-sm btn-outline-success botoes" data-bs-toggle="modal"
            data-bs-target="#modal-nova-subcategoria">
            <i class="bi bi-plus-lg icone-opcao"></i>Subcategoria
        </button>
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

<div class="modal fade" id="modal-nova-subcategoria" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cadastrando uma nova subcategoria</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?= $this->Form->create(null, ['id' => 'form-nova-subcategoria', 'url' => 'subcategorias/add']); ?>
           <?= $this->Form->hidden('categoria_id', ['value' => $categoria->id]);?>
            <?php $this->Form->secure(['nome']); ?>
            <div class="row">
                <div class="col-sm">
                    <label for="nome">Nome da subcategoria</label>
                    <input type="text" class="form-control inputs" id="nome" name="nome">
                </div>
            </div>
        <?= $this->Form->end(['data-type' => 'hidden']);?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary botoes" data-bs-dismiss="modal">Cancelar</button>
        <?=$this->element('Diversos/btnSalvar', ['parametros' => ['atributo' => ['form' => 'form-nova-subcategoria']]])?>
      </div>
    </div>
  </div>
</div>
