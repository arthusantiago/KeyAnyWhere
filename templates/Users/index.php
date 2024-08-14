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
                        <?=$this->element('Diversos/btnEditar', ['parametros' => ['controller' => 'Users', 'id' => $user->id]])?>
                        <a href="#" class="btn btn-sm btn-outline-secondary botoes" role="button" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Iniciar o processo de recuperação de senha">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-exclamation" viewBox="0 0 16 16">
                                <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 11.105V5.383zM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2z"/>
                                <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1.5a.5.5 0 0 1-1 0V11a.5.5 0 0 1 1 0m0 3a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                            </svg> Rec. e-mail
                        </a>
                        <?= $this->element('Diversos/btnExcluir', ['parametros' => ['controller' => 'Users', 'id' => $user->id]])?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<br>
<?= $this->element('paginacao');?>
