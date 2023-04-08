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
    <div class="col-sm">
        <span class="titulo">Usuários</span>
    </div>
    <div class="col-sm text-end">
        <?= $this->element('Diversos/btnNovo', ['parametros' => ['controller' => 'Users', 'texto' => 'Usuário']])?>
    </div>
</div>

<table class="table table-borderless table-striped table-hover">
    <thead>
        <tr class="text-center titulo-coluna-tabela">
            <th><?= $this->Paginator->sort('username') ?></th>
            <th><?= $this->Paginator->sort('email', 'E-mail') ?></th>
            <th></th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr class="text-center">
                <td><?= h($user->username)?></td>
                <td><?= h($user->email)?></td>
                <td><?= $user->root ? '<span class="badge bg-secondary">Root</span>' : '';?></td>
                <td>
                    <?=$this->element('Diversos/btnEditar', ['parametros' => ['controller' => 'Users', 'id' => $user->id]])?>
                    <a href="#" class="btn btn-sm btn-outline-secondary botoes" role="button" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Iniciar o processo de recuperação de senha">
                        <i class="bi bi-envelope-exclamation"></i> Rec. e-mail
                    </a>
                    <?= $this->element('Diversos/btnExcluir', ['parametros' => ['controller' => 'Users', 'id' => $user->id]])?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<br>
<?= $this->element('paginacao');?>
