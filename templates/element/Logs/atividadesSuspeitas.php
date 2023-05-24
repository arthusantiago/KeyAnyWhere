<h2>Atividades suspeitas</h2>

<?php if ($logs): ?>
    <div class='row'>
        <div class="col-sm-4">
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
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 text-center">
            <a class="link-secondary" href="<?=$this->Url->build(['controller' => 'logs', 'action' => 'index'])?>">Lista completa</a>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-sm-4 text-left">
            <p>Tudo dentro dos conformes.</p>
        </div>
    </div>
<?php endif; ?>
