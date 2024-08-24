<div class="row">
    <div class="col-sm mb-3">
        <span class="titulo">Categorias</span>
    </div>
    <div class="col-sm mb-3 text-end">
        <?= $this->element('Diversos/btnNovo', ['parametros' => ['controller' => 'Categorias', 'texto' => 'Categoria']])?>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-borderless table-striped table-hover">
        <thead>
            <tr class="text-center titulo-coluna-tabela">
                <th>Nome</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($query as $categoria): ?>
                <tr class="text-center">
                    <td><?=h($categoria->nomeDescrip())?></td>
                    <td>
                        <div class="btn-group btn-group-sm botoes" role="group">
                            <?=$this->element('Diversos/btnEditar', ['parametros' => ['controller' => 'Categorias', 'id' => $categoria->id]])?>
                            <?=$this->element('Diversos/btnExcluir', ['idRegistro' => $categoria->id, 'tipo' => 'button'])?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<br>
<?= $this->element('paginacao');?>
<?=$this->element('Diversos/modalExcluir', ['parametros' => ['controller' => 'Categorias']])?>
