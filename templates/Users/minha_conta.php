<?php
?>
<nav class="navbar navbar-dark bg-dark">
  <a class="navbar-brand logo" href="#">
    <?= $this->Html->image('login-logo-kaw.png', ['id' => 'login-logo-kaw'])?>
  </a>
</nav>
<br>
<br>
<span class="titulo">Minha Conta</span>
<br><br>
<form>
  <div class="row">
    <div class="col-sm-4">
      Nome do Usuário
      <input type="text" class="form-control inputs">
    </div>
    <div class="col-sm-4">
      E-mail
      <input type="email" class="form-control inputs">
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm-4">
      Nova senha
      <input type="password" class="form-control inputs">
    </div>
    <div class="col-sm-4">
      Confirmação
      <input type="password" class="form-control inputs">
    </div>
  </div>
  <br>
  <div class="row">
      <div class="col-sm text-end">
          <?= $this->element('Diversos/btnSalvar')?>
      </div>
  </div>
</form>