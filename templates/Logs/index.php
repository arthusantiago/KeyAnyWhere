<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Log[]|\Cake\Collection\CollectionInterface $logs
 */
?>
<div class="row">
  <div class="col-sm-auto">
    <?=$this->element(
        'breadcrumb',
        ['caminho' => [
            ['Pages', 'index', 'Home'],
            'Configurações',
            'Logs'
        ]]
    );?>
  </div>
</div>

<div class="row">
    <div class="col-sm mb-3">
        <span class="titulo">Logs</span>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-borderless table-striped table-hover">
        <thead>
            <tr class="text-center titulo-coluna-tabela">
                <th><?=$this->Paginator->sort('created', 'Data')?></th>
                <th><?=$this->Paginator->sort('nivel_severidade', 'Nível Severidade')?></th>
                <th>Mensagem</th>
                <th><?=$this->Paginator->sort('analisado', 'Status')?></th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr class="text-center">
                    <td><?=$log->created?></td>
                    <td><?=$this->element('Logs/badge', ['param' => ['severidade' => $log->nivel_severidade, 'exibicao' => $log->stringNivelSeveridade()]])?></td>
                    <td><?=h($log->mensagemEncurtada())?></td>
                    <td><?=$log->analisado ? 'Analisado' : 'Não analisado';?></td>
                    <td>
                        <div class="btn-group btn-group-sm botoes" role="group">
                            <?php if ($log->analisado): ?>
                                <a class="btn btn-sm btn-outline-secondary" role="button"  href="<?=$this->Url->build(['controller' => 'Logs', 'action' => 'analisado', $log->id])?>" title="Marcar como não analisado">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            <?php else: ?>
                                <a class="btn btn-sm btn-outline-secondary" role="button"  href="<?=$this->Url->build(['controller' => 'Logs', 'action' => 'analisado', $log->id])?>" title="Marcar como analisado">
                                    <i class="bi bi-check-lg"></i>
                                </a>
                            <?php endif; ?>
                            <a class="btn btn-sm btn-outline-secondary" role="button"  href="<?=$this->Url->build(['controller' => 'Logs', 'action' => 'view', $log->id])?>" title="Detalhes">
                                <i class="bi bi-eye-fill"></i>Detalhes
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<br>
<?= $this->element('paginacao');?>
