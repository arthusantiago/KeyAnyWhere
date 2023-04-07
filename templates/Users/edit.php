<div class="row">
  <div class="col-sm-auto">
    <?= $this->element(
        'breadcrumb',
        [
            'caminho' => [
                ['Pages', 'index', 'Home'],
                'Configurações',
                ['Users', 'index', 'Usuários'],
                'Editando'
            ]
        ]
    );?>
  </div>
</div>
<div class="row">
    <div class="col-sm-auto">
        <span class="titulo">Editando o usuário</span>
    </div>
</div>
<br>
<?= $this->Form->create($user) ?>
    <?php $this->Form->secure([
        'username',
        'email',
        'password',
    ]); ?>
    <div class="row">
        <div class="col-sm-4">
            <label for="username">Nome do Usuário</label>
            <input type="text" class="form-control inputs" id="username" name="username" autocomplete="nickname" maxlength="50" value="<?=$user->username?>" required>
        </div>
        <div class="col-sm-4">
            <label for="email">E-mail</label>
            <input type="email" class="form-control inputs" id="email" name="email" autocomplete="email" value="<?=$user->email?>" required>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-sm-4">
            <label for="password">Nova senha</label>
            <input type="password" id="password" name="password" class="form-control inputs" autocomplete="new-password" minlength="12">
        </div>
    </div>
    <div class="row">
        <div class="col-sm text-end">
            <?= $this->element('Diversos/btnSalvar') ?>
        </div>
    </div>
<?=$this->Form->end(['data-type' => 'hidden']); ?>
