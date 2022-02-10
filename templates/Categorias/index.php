<div class="row">
    <div class="col-sm">
        <span class="titulo">Categorias</span>
    </div>
    <div class="col-sm text-end">
        <?= $this->element('Diversos/btnNovo', ['parametros' => ['controller' => 'Categoria', 'texto' => 'Categoria']])?>
    </div>
</div>

<table class="table table-borderless table-striped table-hover">
    <thead>
        <tr class="text-center titulo-coluna-tabela">
            <th>Nome</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($query as $categoria): ?>
            <tr class="text-center">
                <td><?= h($categoria->nome) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>