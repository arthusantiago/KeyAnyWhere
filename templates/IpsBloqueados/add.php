<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\IpsBloqueado $ipsBloqueado
 */
?>
<div class="row">
  <div class="col-sm-auto">
    <?= $this->element(
      'breadcrumb',
      ['caminho' => [
        ['Pages', 'index', 'Home'],
        'Configurações',
        ['IpsBloqueados', 'index', 'IPs Bloqueados'],
        'Bloqueando IP'
      ]]
    ); ?>
  </div>
</div>

<div class="row">
  <div class="col-sm mb-3">
    <span class="titulo">Adicionando novo IP ao bloqueio</span>
  </div>
</div>

<?= $this->Form->create($ipsBloqueado); ?>
<?php $this->Form->secure(['ip']); ?>
<div class="row">
  <div class="col-sm-5 mb-3">
    <label for="nome">IP</label>
    <input type="text" class="form-control inputs" id="ip" name="ip" title="Insira um endereço IP válido" maxlength="39"
      placeholder="111.222.333.444 ou 0000:0000:0000:0000:0000:0000:0000:0000" required>
  </div>
</div>
<div class="row">
  <div class="col-sm mb-3 text-end">
    <?= $this->element('Diversos/btnSalvar') ?>
  </div>
</div>
<?= $this->Form->end(['data-type' => 'hidden']); ?>