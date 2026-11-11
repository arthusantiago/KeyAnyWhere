<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Auditoria $auditoria
 * @var \App\Model\Entity\User $userLogado
 */

// Só os nomes: o valor antes/depois é mascarado na gravação (o KAW guarda segredos).
$camposAlterados = implode(', ', array_map('ucfirst', array_keys((array)$auditoria->alteracoes)));
?>
<div class="row">
  <div class="col-sm-auto">
    <?=$this->element(
        'breadcrumb',
        ['caminho' => [
            ['Pages', 'home', 'Home'],
            'Configurações',
            ['Auditorias', 'index', 'Auditoria'],
            'Visualizando'
        ]]
    );?>
  </div>
</div>

<div class="row">
    <div class="col-sm-auto mb-3">
        <span class="titulo"><?=__('Detalhes')?></span>
    </div>
</div>

<div class="row">
    <div class="table-responsive">
        <table class="table table-md">
            <tbody>
                <tr>
                    <th><?=__('Contexto')?></th>
                    <td><?=h(ucfirst($auditoria->contexto))?></td>
                </tr>
                <tr>
                    <th><?=__('Ação')?></th>
                    <td><?=h(ucfirst($auditoria->acao))?></td>
                </tr>
                <tr>
                    <th><?=__('Registro')?></th>
                    <td><?=h($auditoria->id_entidade)?></td>
                </tr>
                <tr>
                    <th><?=__('Usuário')?></th>
                    <td><?=h($auditoria->descricaoUsuario())?></td>
                </tr>
                <?php if ($userLogado->root): ?>
                <tr>
                    <th><?=__('IP de origem')?></th>
                    <td><?=h($auditoria->ip_origem ?: '-')?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?=__('Data')?></th>
                    <td><?=h($auditoria->created)?></td>
                </tr>
                <tr>
                    <th><?=__('Campos alterados')?></th>
                    <td><?=h($camposAlterados ?: '-')?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
