<div class="row">
  <div class="col-sm-auto">
    <?=$this->element(
        'breadcrumb',
        ['caminho' => [
            ['Pages', 'index', 'Home'],
            'Configurações',
            ['IpsBloqueados', 'index', 'IPs Bloqueados'],
            'Bloqueando IP'
        ]]
    );?>
  </div>
</div>

<div class="row">
    <div class="col-sm">
        <span class="titulo">Adicionando novo IP ao bloqueio</span>
    </div>
</div>

<br>

<?= $this->Form->create($ipsBloqueado); ?>
  <?php $this->Form->secure(['ip']); ?>
  <div class="row">
    <div class="col-sm-4">
      <label for="nome">IP</label>
      <input type="text" class="form-control inputs" id="ip" name="ip" title="Insira um endereço IP válido" maxlength="39" 
      placeholder="111.222.333.444 ou 0000:0000:0000:0000:0000:0000:0000:0000" required>
    </div>
  </div>
  <br>
  <div class="d-grid gap-2 d-md-flex justify-content-md-end">
    <button class="btn btn-primary botoes" type="submit">Salvar</button>
  </div>
<?= $this->Form->end(['data-type' => 'hidden']); ?>
