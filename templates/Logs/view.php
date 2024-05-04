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
                    <th><?='Evento'?></th>
                    <td><?=$log->evento?></td>
                </tr>
                <tr>
                    <th><?='Recurso'?></th>
                    <td><?=$log->recurso?></td>
                </tr>
                <tr>
                    <th><?='Ip Origem'?></th>
                    <td><?= h($log->ip_origem) ?></td>
                </tr>
                <tr>
                    <th><?='Usuário'?></th>
                    <td><?=h($log->usuario)?></td>
                </tr>
                <tr>
                    <th><?='Mensagem'?></th>
                    <td><?=h($log->mensagem) ?></td>
                </tr>
                <tr>
                    <th><?='Nivel Severidade'?></th>
                    <td><?=$log->stringNivelSeveridade();?></td>
                </tr>
                <tr>
                    <th><?='Data'?></th>
                    <td><?=$log->created?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
