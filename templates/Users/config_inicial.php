<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="row">
    <div class="col-sm mb-3">
        <h2 class="text-center titulo">(1 de 2) <?=__('Usuário de acesso')?></h2>
    </div>
</div>
<div class="row">
    <div class="col-sm mb-3">
        <p class="text-center"><?=__('Vamos criar o primeiro usuário do sistema, que também será o usuário <b>administrador</b>.')?></p>
    </div>
</div>


<?= $this->Form->create(null, ['url' => ['controller' => 'users', 'action' => 'configInicial']]); ?>
    <?php $this->Form->secure(['username', 'email', 'password']); ?>
    <div class="row justify-content-center">
        <div class="col-sm-4 mb-3">
            <label for="username" class="form-label"><?=__('Nome')?></label>
            <input type="text" class="form-control inputs" id="username" name="username" autocomplete="nickname" placeholder="Fulano Silva">
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-sm-4 mb-3">
            <label for="email" class="form-label"><?=__('E-mail')?>
                <?=$this->element('tooltip', ['texto' => 'E-mail utilizado para fazer login na ferramenta']);?>
            </label>
            <input type="email" class="form-control inputs" id="email" name="email" autocomplete="email" placeholder="seu@email.com.br">
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-sm-4 mb-3">
            <?=$this->element('inputSenhaUser')?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm text-center">
            <?= $this->element('Diversos/btnSalvar')?>
        </div>
    </div>
<?= $this->Form->end(['data-type' => 'hidden']); ?>
