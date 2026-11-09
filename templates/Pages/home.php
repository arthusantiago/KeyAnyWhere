<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $logs
 * @var mixed $sessoesAtivas
 * @var \App\Model\Entity\User $userLogado
 */
?>
<br>
<div class='row'>
    <div class="col-sm mb-3 text-center">
        <h1>Minha Segurança</h1>
    </div>
</div>
<div class='row'>
    <div class="col-sm mb-3">
        <h3>Atividades suspeitas</h3>
        <?php if ($logs): ?>
            <ul class="list-group">
                <?php foreach($logs as $log): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php
                            echo $log->stringNivelSeveridade() .
                            $this->element('Logs/badge', ['param' => ['severidade' => $log->nivel_severidade, 'exibicao' => $log->quantidade]])
                        ?>
                    </li>
                <?php endforeach;?>
            </ul>
            <?php if ($userLogado->root): ?>
                <div class="text-center">
                    <a class="link-secondary" href="<?=$this->Url->build(['controller' => 'logs', 'action' => 'index'])?>">Lista completa</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Aparentemente tranquilo. Continue atento &#128373;&#127997;</p>
        <?php endif; ?>
    </div>
    <div class="col-sm mb-3">
        <h3>Usuários ativos</h3>
        <ul class="list-group">
        <?php foreach($sessoesAtivas as $sessao): ?>
            <li class="list-group-item"><?=h($sessao->created . ' - ' . $sessao->user->usernameEncurtado())?></li>
        <?php endforeach;?>
        </ul>
    </div>
</div>
<br><br>
