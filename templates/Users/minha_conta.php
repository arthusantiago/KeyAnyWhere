<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
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
            ['Users', 'index', 'Usuários'],
            'Minha conta'
          ]
        ]
    );?>
  </div>
</div>
<div class="row">
  <div class="col-sm-auto mb-3">
    <span class="titulo"><?=__('Minha conta')?></span>
    <?=$user->root?'<span class="badge bg-secondary">Administrador</span>': '' ;?>
  </div>
</div>

<?= $this->Form->create($user) ?>
  <?php $this->Form->secure(['username', 'email', 'password']); ?>
  <div class="row">
    <div class="col-sm mb-3">
      <label for="username"><?=__('Nome')?></label>
      <input type="text" class="form-control inputs" id="username" name="username" value="<?=h($user->username)?>" maxlength="50">
    </div>
    <div class="col-sm mb-3">
        <label for="email"><?=__('E-mail')?>
          <?=$this->element('tooltip', ['texto' => 'E-mail utilizado para fazer login na ferramenta']);?>
        </label>
      <input type="email" class="form-control inputs" id="email" name="email" value="<?=h($user->email)?>" maxlength="100" autocomplete="email">
    </div>
  </div>
  <div class="row">
    <div class="col-sm-6 mb-3">
      <?= $this->element('inputSenhaUser')?>
    </div>
  </div>
  <div class="row">
    <div class="col-sm mb-3 text-end">
      <?= $this->element('Diversos/btnSalvar')?>
    </div>
  </div>
<?= $this->Form->end(['data-type' => 'hidden']);?>
<hr/>

<div class="row">
  <div class="col-sm-auto mb-3">
    <span class="titulo"><?=__('Segurança')?></span>
  </div>
</div>
<div class="row">
  <div class="col-sm-auto mb-3">
    <button type="button" class="btn btn-outline-secondary btn-gerar-qrcode" data-bs-toggle="modal" data-bs-target="#TFAModal" data-qrcode-user-id="<?=$user->id?>">
      <?=__('Segundo fator de autenticação')?>
    </button>
  </div>
</div>
<?= $this->element('Users/tfa')?>
<hr/>

<div class="row">
  <div class="col-sm-auto mb-3">
    <span class="titulo"><?=__('Sessões')?></span>
  </div>
</div>
<div class="row">
<?= $this->element('Users/sessions')?>
</div>
<br><br>
<?=$this->element('Diversos/modalExcluir', ['parametros' => ['controller' => 'Users', 'action' => 'finalizarSessao']])?>
