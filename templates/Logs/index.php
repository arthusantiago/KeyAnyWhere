<div class="row">
  <div class="col-sm-auto">
    <?=$this->element(
        'breadcrumb',
        ['caminho' => [
            ['Pages', 'index', 'Home'],
            'Configurações',
            'Logs',
            'Listagem'
        ]]
    );?>
  </div>
</div>

<div class="row">
    <div class="col-sm">
        <span class="titulo">Logs</span>
    </div>
</div>

<table class="table table-borderless table-striped table-hover">
    <thead>
        <tr class="text-center titulo-coluna-tabela">
            <th><?=$this->Paginator->sort('created', 'Data')?></th>
            <th><?=$this->Paginator->sort('nivel_severidade', 'Nível Severidade')?></th>
            <th><?=$this->Paginator->sort('analisado', 'Status')?></th>
            <th>Mensagem</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logs as $log): ?>
            <tr class="text-center">
                <td><?= h($log->created) ?></td>
                <td><?=$this->element('Logs/badge', ['param' => ['severidade' => $log->nivel_severidade, 'exibicao' => $log->stringNivelSeveridade()]])?></td>
                <td><?= $log->analisado ? 'Analisado' : 'Não analisado';?></td>
                <td><?= h($log->mensagem)?></td>
                <td>
                    <?= $this->Form->postLink(
                        $log->analisado ? '<i class="bi bi-x-lg"></i>' : '<i class="bi bi-check-lg"></i>',
                        ['controller' => 'logs', 'action' => 'analisado', $log->id],
                        [
                            'confirm' => 'Realmente deseja alterar o status do log?',
                            'class' => 'btn btn-sm btn-secondary',
                            'role' => 'button',
                            'title' => $log->analisado ? 'Marcar como não analisado' : 'Marcar como analisado',
                            'data' => ['analisado' => $log->analisado ? 0 : 1],
                            'escapeTitle' => false
                        ]
                    ) ?>
                    <a class="btn btn-sm btn-secondary" role="button"  href="<?=$this->Url->build(['controller' => 'Logs', 'action' => 'view', $log->id])?>" title="Detalhes">
	                	<i class="bi bi-eye-fill"></i>
	                </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<br>
<?= $this->element('paginacao');?>
