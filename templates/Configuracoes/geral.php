<?php
/**
 * @var \App\View\AppView $this
 * @var array{fusoHorario: string, idioma: string} $preferencias
 * @var array<string, string> $idiomas
 * @var array<int, string> $fusosHorarios
 */
?>
<div class="row">
  <div class="col-sm-auto">
    <?= $this->element(
      'breadcrumb',
      [
        'caminho' => [
          ['Pages', 'home', 'Home'],
          'Configurações',
          'Configurações gerais'
        ]
      ]
    ); ?>
  </div>
</div>
<div class="row">
  <div class="col-sm-auto mb-3">
    <span class="titulo"><?=__('Configurações gerais')?></span>
  </div>
</div>

<?= $this->Form->create(null, ['url' => ['controller' => 'Configuracoes', 'action' => 'geral']]) ?>
<?php $this->Form->secure(['idioma', 'fusoHorario']); ?>
<div class="row">
  <div class="col-sm-3 mb-3">
    <label for="idioma"><?=__('Idioma')?></label>
    <select class="form-select inputs" id="idioma" name="idioma" required>
      <?php foreach ($idiomas as $id => $nome): ?>
        <option value="<?= h($id) ?>" <?= $id === $preferencias['idioma'] ? 'selected' : '' ?>><?= h($nome) ?></option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="col-sm-4 mb-3">
    <label for="fusoHorario"><?=__('Fuso horário')?>
      <?=$this->element('tooltip', ['texto' => 'Datas e horários são exibidos e registrados neste fuso horário.']);?>
    </label>
    <select class="form-select inputs" id="fusoHorario" name="fusoHorario" required>
      <?php foreach ($fusosHorarios as $fuso): ?>
        <option value="<?= h($fuso) ?>" <?= $fuso === $preferencias['fusoHorario'] ? 'selected' : '' ?>><?= h($fuso) ?></option>
      <?php endforeach ?>
    </select>
  </div>
</div>
<div class="row">
  <div class="col-sm mb-3 text-end">
    <?= $this->element('Diversos/btnSalvar') ?>
  </div>
</div>
<?= $this->Form->end(['data-type' => 'hidden']); ?>
