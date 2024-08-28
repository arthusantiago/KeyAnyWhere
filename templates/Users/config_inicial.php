<br>
<h1 class="text-center">Bem-vindo KeyAnyWhere!</h1>
<p class="text-center">Neste passo a passo vamos executar a configuração inicial da ferramenta.</p>
<br>
<h2 class="text-center titulo">Usuário de acesso</h2>
<p class="text-center">Vamos criar o primeiro usuário do sistema, que também será o usuário <b>administrador</b>.</p>
<?= $this->Form->create(null, ['url' => ['controller' => 'users', 'action' => 'configInicial']]); ?>
    <?php $this->Form->secure(['username', 'email', 'password']); ?>
    <div class="row justify-content-center">
        <div class="col-sm-4 mb-3">
            <label for="username" class="form-label">Nome</label>
            <input type="text" class="form-control inputs" id="username" name="username" autocomplete="nickname" placeholder="Fulano Silva">
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-sm-4 mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control inputs" id="email" name="email" autocomplete="email" placeholder="seu@email.com.br">
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-sm-4 mb-3">
            <?=$this->element('inputSenhaUser')?>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-sm-1">
            <?= $this->element('Diversos/btnSalvar')?>
        </div>
    </div>
<?= $this->Form->end(['data-type' => 'hidden']); ?>
