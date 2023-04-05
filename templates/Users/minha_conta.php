<div class="row">
  <div class="col-sm-auto">
    <span class="titulo">Minha Conta</span>
  </div>
</div>

<br>

<?= $this->Form->create($user) ?>
<?php $this->Form->secure([
  'username',
  'email',
  'password',
  'tfa_ativo'
]); ?>
<div class="row">
  <div class="col-sm-4">
    <label for="username">Nome do Usuário</label>
    <input type="text" class="form-control inputs" id="username" name="username" value="<?= $user->username ?>" autocomplete="nickname">
  </div>
  <div class="col-sm-4">
    <label for="email">E-mail</label>
    <input type="email" class="form-control inputs" id="email" name="email" value="<?= $user->email ?>" autocomplete="email">
  </div>
</div>
<br>
<div class="row">
  <div class="col-sm-4">
    <label for="password">Nova senha</label>
    <input type="password" id="password" name="password" class="form-control inputs" autocomplete="new-password" minlength="12">
  </div>
</div>
<br>
<div class="row">
  <div class="col-sm-auto">
    <span class="titulo">Segurança</span>
  </div>
</div>
<br>
<div class="row">
  <div class="col-sm-4">
    <div class="form-check">
      <input class="form-check-input" type="radio" name="tfa_ativo" id="ativar2FA" value="1" <?=$user->tfa_ativo?'checked':'';?>>
      <label class="form-check-label" for="ativar2FA">
        Ativar 2FA
      </label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="radio" name="tfa_ativo" id="desativar2FA" value="0" <?=$user->tfa_ativo==0?'checked':'';?>>
      <label class="form-check-label" for="desativar2FA">
        Desativar 2FA
      </label>
    </div>
  </div>
  <?php if($user->tfa_ativo): ?>
    <div class="col-sm-4">
      <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#TFAModal" 
        onclick="obterQrCode2FA('imagemQrCode', '<?=$this->Url->build(['controller' => 'Users', 'action' => 'geraQrCode2fa'], ['fullBase' => true])?>')">
        Configurar a Autenticação de Dois Fatores (2FA)
      </button>
    </div>
  <?php endif; ?>
</div>
<br>
<div class="row">
  <div class="col-sm text-end">
    <?= $this->element('Diversos/btnSalvar')?>
  </div>
</div>
<?= $this->Form->end(['data-type' => 'hidden']);?>

<div class="modal fade" id="TFAModal" tabindex="-1" aria-labelledby="TFAModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="TFAModalLabel">Configurando a Autenticação em Dois Fatores (2FA)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-sm text-center">
            <div id="imagemQrCode"></div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm text-center">
            <button type="button" class="btn btn-outline-secondary"
              onclick="obterQrCode2FA(
                'imagemQrCode',
                '<?=$this->Url->build(['controller' => 'Users', 'action' => 'geraQrCode2fa', '?' => ['novoQrCode' => '1']], ['fullBase' => true])?>',
              )">
              Gerar novo QrCode
            </button>
          </div>
        </div>
        <div class="row">
            <p><b>Observação</b><br>A nova senha 2FA gerada, valerá para o próximo login.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>