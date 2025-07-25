<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $ipsBloqueados
 */
?>
<div class="row">
  <div class="col-sm-auto">
    <?=$this->element(
        'breadcrumb',
        ['caminho' => [
            ['Pages', 'index', 'Home'],
            'Configurações',
            'IPs Bloqueados',
            'Listagem'
        ]]
    );?>
  </div>
</div>

<div class="row">
    <div class="col-sm mb-3">
        <span class="titulo">IPs Bloqueados</span>
    </div>
    <div class="col-sm mb-3 text-end">
        <?= $this->element('Diversos/btnNovo', ['parametros' => ['controller' => 'IpsBloqueados', 'texto' => 'IP']])?>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-borderless table-striped table-hover">
        <thead>
            <tr class="text-center titulo-coluna-tabela">
                <th><?=$this->Paginator->sort('created', 'Data bloqueio')?></th>
                <th><?=$this->Paginator->sort('ip', 'IP')?></th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ipsBloqueados as $ip): ?>
                <tr class="text-center">
                    <td><?=h($ip->created)?></td>
                    <td><?=h($ip->ip)?></td>
                    <td><?=$this->element('Diversos/btnExcluir', ['idRegistro' => $ip->id, 'tipo' => 'button'])?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<br>
<?= $this->element('paginacao');?>
<?=$this->element('Diversos/modalExcluir', ['parametros' => ['controller' => 'IpsBloqueados']])?>
