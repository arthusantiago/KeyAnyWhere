<br>
<div class='row'>
    <div class="col-sm text-center">
        <h1>Minha Segurança</h1>
    </div>
</div>
<br>
<div class='row'>
    <div class="col-sm-4">
        <h2>Atividades suspeitas</h2>
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
            <div class="text-center">
                <a class="link-secondary" href="<?=$this->Url->build(['controller' => 'logs', 'action' => 'index'])?>">Lista completa</a>
            </div>
        <?php else: ?>
            <p>Aparentemente tranquilo. Continue atento &#128373;&#127997;</p>
        <?php endif; ?>
    </div>
    <div class="col-sm-auto">
        <h2>Últimos IPs bloqueados</h2>
        <?php if ($ipsBloqueados): ?>
            <ul class="list-group">
                <?php foreach($ipsBloqueados as $ip): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?=$ip->created . ' - ' . $ip->ip?>
                        <?php if ($ip->created->wasWithinLast('24 hours')): ?>
                            <span class='badge rounded-pill bg-secondary'>recente</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach;?>
            </ul>
            <div class="text-center">
                <a class="link-secondary" href="<?=$this->Url->build(['controller' => 'IpsBloqueados', 'action' => 'index'])?>">Lista completa</a>
            </div>
        <?php else: ?>
            <p>Nenhum IP bloqueado</p>
        <?php endif; ?>
    </div>
</div>
