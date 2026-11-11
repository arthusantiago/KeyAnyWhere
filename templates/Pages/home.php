<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Auditoria> $ultimasAlteracoes
 * @var iterable<\App\Model\Entity\EventoSeguranca> $ultimosEventos
 * @var mixed $sessoesAtivas
 * @var \App\Model\Entity\User $userLogado
 */
?>
<br>
<div class='row'>
    <div class="col-sm mb-3 text-center">
        <h1><?=__('Minha Segurança')?></h1>
    </div>
</div>
<div class='row'>
    <div class="col-sm mb-3">
        <h3><?=__('Últimas alterações')?></h3>
        <?php if (!$ultimasAlteracoes->isEmpty()): ?>
            <ul class="list-group">
                <?php foreach($ultimasAlteracoes as $auditoria): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><?=h($auditoria->created . ' - ' . ucfirst($auditoria->contexto) . ' (' . $auditoria->acao . ') por ' . $auditoria->descricaoUsuario())?></span>
                        <a class="link-secondary" href="<?=$this->Url->build(['controller' => 'Auditorias', 'action' => 'view', $auditoria->id])?>" title="<?=__('Detalhes')?>"><i class="bi bi-eye"></i></a>
                    </li>
                <?php endforeach;?>
            </ul>
            <div class="text-center">
                <a class="link-secondary" href="<?=$this->Url->build(['controller' => 'Auditorias', 'action' => 'index'])?>"><?=__('Lista completa')?></a>
            </div>
        <?php else: ?>
            <p><?=__('Nenhuma alteração registrada ainda.')?></p>
        <?php endif; ?>
    </div>
    <div class="col-sm mb-3">
        <h3><?=__('Eventos de segurança')?></h3>
        <?php if (!$ultimosEventos->isEmpty()): ?>
            <ul class="list-group">
                <?php foreach($ultimosEventos as $evento): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><?=h($evento->created . ' - ' . $evento->titulo() . ' (' . $evento->descricaoUsuario() . ')')?></span>
                        <span class="badge <?=h($evento->classeNivel())?>"><?=h($evento->nivel)?></span>
                    </li>
                <?php endforeach;?>
            </ul>
            <div class="text-center">
                <a class="link-secondary" href="<?=$this->Url->build(['controller' => 'EventosSeguranca', 'action' => 'index'])?>"><?=__('Lista completa')?></a>
            </div>
        <?php else: ?>
            <p><?=__('Nenhum evento de segurança registrado.')?></p>
        <?php endif; ?>
    </div>
</div>
<div class='row'>
    <div class="col-xl-6 mb-3">
        <h3><?=__('Usuários ativos')?></h3>
        <ul class="list-group">
        <?php foreach($sessoesAtivas as $sessao): ?>
            <li class="list-group-item"><?=h($sessao->created . ' - ' . $sessao->user->usernameEncurtado())?></li>
        <?php endforeach;?>
        </ul>
    </div>
</div>
<br><br>
