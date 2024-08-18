<!-- usado para marcar em qual categoria o usuário está -->
<input type="hidden" id="id-categoria-selecionada" value="<?=$categoria->id?>">
<?=$this->Html->script('minified/categorias.min.js', ['block' => 'script-first-load']);?>

<div class="row">
    <div class="col-sm mb-3">
        <span class="titulo"><?=h($categoria->nomeDescrip())?></span>
    </div>
    <div class="col-sm mb-3 text-end">
        <?=$this->element('Diversos/btnNovo', ['parametros' => ['controller' => 'entradas', 'texto' => 'Entrada']])?>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-borderless table-striped table-hover">
        <thead>
            <tr class="text-center titulo-coluna-tabela">
                <th>Titulo</th>
                <th>Opções</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entradas as $entrada): ?>
                <tr class="text-center">
                    <?php if (empty($entrada->linkDescrip())): ?>
                        <td><?=h($entrada->tituloDescrip())?></td>
                    <?php else: ?>
                        <td>
                            <a href="<?=h($entrada->linkDescrip())?>" target="_blank" class="text-decoration-none">
                                <?=h($entrada->tituloDescrip())?>
                            </a>
                        </td>
                    <?php endif; ?>
                    <td>
                        <div class="btn-group btn-group-sm botoes" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-clipboard" data-clipboard-entrada-id="<?=$entrada->id?>" data-clipboard-tipo="user">
                                <i class="bi bi-person-fill icone-opcao"></i>Usuário
                            </button>
                            <button type="button" class="btn btn-outline-secondary  btn-clipboard" data-clipboard-entrada-id="<?=$entrada->id?>" data-clipboard-tipo="password">
                                <i class="bi bi-key-fill icone-opcao"></i>Senha
                            </button>
                            <div class="btn-group" role="group">
                                <button id="btnGroupDrop" type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"></button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="<?=$this->Url->build(['controller' => 'Entradas', 'action' => 'edit', $entrada->id])?>">
                                            <i class="bi bi-pencil-fill icone-opcao"></i>Editar
                                        </a>
                                    </li>
                                    <li><?=$this->element('Diversos/btnExcluir', ['idRegistro' => $entrada->id])?></li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<br>
<?=$this->element('paginacao');?>
<?=$this->element('Diversos/modalExcluir', ['parametros' => ['controller' => 'Entradas']])?>
