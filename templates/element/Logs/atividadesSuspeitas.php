<h2>Atividades suspeitas</h2>

<?php if ($logs): ?>
    <div class='row'>
        <div class="col-sm-4">
            <ul class="list-group">
                <?php foreach($logs as $log): ?>
                    <?php if ($log->nivel_severidade == 0):?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            EMERGENCY <span class="badge bg-danger rounded-pill"><?=$log->quantidade?></span>
                        </li>
                        <?php continue; ?>
                    <?php endif; ?>

                    <?php if ($log->nivel_severidade == 1):?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ALERT <span class="badge rounded-pill" style="background-color: #ff6c00;"><?=$log->quantidade?></span>
                        </li>
                        <?php continue; ?>
                    <?php endif; ?>

                    <?php if ($log->nivel_severidade == 2):?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            CRITICAL <span class="badge bg-warning rounded-pill"><?=$log->quantidade?></span>
                        </li>
                        <?php continue; ?>
                    <?php endif; ?>

                    <?php if ($log->nivel_severidade == 3):?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ERROR <span class="badge rounded-pill" style="background-color: #b03878;"><?=$log->quantidade?></span>
                        </li>
                        <?php continue; ?>
                    <?php endif; ?>

                    <?php if ($log->nivel_severidade == 4):?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            WARNING <span class="badge bg-primary rounded-pill"><?=$log->quantidade?></span>
                        </li>
                        <?php continue; ?>
                    <?php endif; ?>

                    <?php if ($log->nivel_severidade == 5):?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            NOTICE <span class="badge bg-secondary rounded-pill"><?=$log->quantidade?></span>
                        </li>
                        <?php continue; ?>
                    <?php endif; ?>
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

