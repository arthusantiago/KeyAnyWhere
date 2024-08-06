<div class="row">
  <div class="col-sm-auto">
    <?= $this->element(
        'breadcrumb',
        [
          'caminho' => [
            ['Pages', 'index', 'Home'],
            'Configurações',
            ['Users', 'index', 'Usuários'],
            'Minha conta'
          ]
        ]
    );?>
  </div>
</div>
<div class="row">
  <div class="col-sm-auto">
    <span class="titulo">Minha Conta</span>
    <?=$user->root?'<span class="badge bg-secondary">Usuário root</span>':'';?>
  </div>
</div>
<br>

<?= $this->Form->create($user) ?>
  <?php $this->Form->secure(['username', 'email', 'password']); ?>
  <div class="row">
    <div class="col-sm">
      <label for="username">Nome do Usuário</label>
      <input type="text" class="form-control inputs" id="username" name="username" value="<?=h($user->username)?>" maxlength="50">
    </div>
    <div class="col-sm">
      <label for="email">E-mail (login e rec. de senha)</label>
      <input type="email" class="form-control inputs" id="email" name="email" value="<?=h($user->email)?>" maxlength="100" autocomplete="email">
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm-6">
      <?= $this->element('inputSenhaUser')?>
    </div>
  </div>
  <div class="row">
    <div class="col-sm text-end">
      <?= $this->element('Diversos/btnSalvar')?>
    </div>
  </div>
<?= $this->Form->end(['data-type' => 'hidden']);?>
<hr/>

<div class="row">
  <div class="col-sm-auto">
    <span class="titulo">Segurança</span>
  </div>
</div>
<br>
<div class="row">
  <div class="col-sm-auto">
    <button type="button" class="btn btn-outline-secondary btn-gerar-qrcode" data-bs-toggle="modal" data-bs-target="#TFAModal" data-qrcode-user-id="<?=$user->id?>">
      Autenticação de Dois Fatores
    </button>
  </div>
</div>
<?= $this->element('Users/tfa')?>
<hr/>

<div class="row">
  <div class="col-sm-auto">
    <span class="titulo">Sessões</span>
  </div>
</div>
<br>
<div class="row">
<?= $this->element('Users/sessions')?>
</div>
<br><br>
