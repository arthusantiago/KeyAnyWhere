<div class="row">
    <div class="col-sm-auto">
        <?= $this->element(
            'breadcrumb',
            [
                'caminho' => [
                    ['Pages', 'index', 'Home'],
                    'Configurações',
                    ['Users', 'index', 'Usuários'],
                    'Novo'
                ]
            ]
        ); ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-auto mb-3">
        <span class="titulo">Novo usuário</span>
    </div>
</div>

<?= $this->Form->create($user) ?>
<?php $this->Form->secure([
    'username',
    'email',
    'password',
]); ?>
<div class="row">
    <div class="col-sm-5 mb-3">
        <label for="username">Nome do Usuário</label>
        <input type="text" class="form-control inputs" id="username" name="username" maxlength="50" required>
    </div>
    <div class="col-sm-5 mb-3>
            <label for=" email">E-mail</label>
        <input type="email" class="form-control inputs" id="email" name="email" autocomplete="email" maxlength="100" required>
    </div>
</div>
<div class="row">
    <div class="col-sm-5 mb-3">
        <?= $this->element('inputSenhaUser') ?>
    </div>
</div>
<div class="row">
    <div class="col-sm text-end">
        <?= $this->element('Diversos/btnSalvar') ?>
    </div>
</div>
<?= $this->Form->end(['data-type' => 'hidden']); ?>