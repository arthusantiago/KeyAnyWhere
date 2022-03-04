<div class="row">
    <div class="col-sm-auto">
        <span class="titulo">Minha Conta</span>
    </div>
</div>

<br>

<?= $this->Form->create($user)?>
  <?php $this->Form->secure([
    'username',
    'email',
    'password'
  ]);?>
  <div class="row">
    <div class="col-sm-4">
      <label for="username">Nome do Usuário</label>
      <input type="text" class="form-control inputs" id="username" name="username" value="<?=$user->username?>" autocomplete="nickname">
    </div>
    <div class="col-sm-4">
      <label for="email">E-mail</label>
      <input type="email" class="form-control inputs" id="email" name="email" value="<?=$user->email?>" autocomplete="email">
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm-4">
      <label for="password">Nova senha</label>
      <input type="password" id="password" name="password" class="form-control inputs" autocomplete="new-password" minlength="8">
    </div>
  </div>
  <br>
  <div class="row">
      <div class="col-sm text-end">
          <?= $this->element('Diversos/btnSalvar')?>
      </div>
  </div>
<?= $this->Form->end(['data-type' => 'hidden']);?>
