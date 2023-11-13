<div class="row">
  <div class="col-sm-auto">
    <?=$this->element(
        'breadcrumb',
        ['caminho' => [
            ['Pages', 'index', 'Home'],
            'Configurações',
            ['Logs', 'index', 'Logs'],
            'Visualização'
        ]]
    );?>
  </div>
</div>

<div class="row">
    <div class="col-sm-auto">
        <span class="titulo">Detalhes do log</span>
    </div>
</div>

<div class="row">
    <div>
        <table class="table table-md">
            <tbody>
                <tr>
                    <th><?= __('Evento') ?></th>
                    <td><?= h($log->evento) ?></td>
                </tr>
                <tr>
                    <th><?= __('Recurso') ?></th>
                    <td><?= h($log->recurso) ?></td>
                </tr>
                <tr>
                    <th><?= __('Ip Origem') ?></th>
                    <td><?= h($log->ip_origem) ?></td>
                </tr>
                <tr>
                    <th><?= __('Usuário') ?></th>
                    <td><?= h($log->usuario) ?></td>
                </tr>
                <tr>
                    <th><?= __('Mensagem') ?></th>
                    <td><?= h($log->mensagem) ?></td>
                </tr>
                <tr>
                    <th><?= __('Nivel Severidade') ?></th>
                    <td><?=$log->stringNivelSeveridade();?></td>
                </tr>
                <tr>
                    <th><?= __('Data') ?></th>
                    <td><?= h($log->created) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
