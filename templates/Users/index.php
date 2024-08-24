<div class="row">
  <div class="col-sm-auto">
    <?= $this->element(
        'breadcrumb',
        [
            'caminho' => [
                ['Pages', 'index', 'Home'],
                'Configurações',
                'Usuários',
                'Listagem'
            ]
        ]
    );?>
  </div>
</div>

<div class="row">
    <div class="col-sm mb-3">
        <span class="titulo">Usuários</span>
    </div>
    <div class="col-sm mb-3 text-end">
        <?= $this->element('Diversos/btnNovo', ['parametros' => ['controller' => 'Users', 'texto' => 'Usuário']])?>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-borderless table-striped table-hover">
        <thead>
            <tr class="text-center titulo-coluna-tabela">
                <th><?= $this->Paginator->sort('username') ?></th>
                <th><?= $this->Paginator->sort('email', 'E-mail') ?></th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr class="text-center">
                <td><?= h($user->username)?></td>
                <td><?= h($user->email)?></td>
                <td>
                    <div class="btn-group btn-group-sm botoes" role="group">
                        <?=$this->element('Diversos/btnEditar', ['parametros' => ['controller' => 'Users', 'id' => $user->id]])?>
                        <div class="btn-group" role="group">
                            <button id="btnGroupDrop" type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"></button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#"><i class="bi bi-envelope"></i>Rec. e-mail</a>
                                </li>
                                <li><?=$this->element('Diversos/btnExcluir', ['idRegistro' => $user->id])?></li>
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
<?= $this->element('paginacao');?>
<?=$this->element('Diversos/modalExcluir', ['parametros' => ['controller' => 'Users']])?>