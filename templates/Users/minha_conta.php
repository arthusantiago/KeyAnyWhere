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
  'password'
]); ?>
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
<br>
<div class="row">
  <div class="col-sm-auto">
    <span class="titulo">Segurança</span>
  </div>
</div>
<br>
<div class="row">
    <div class="col-sm-4">
      <button type="button" class="btn btn-outline-secondary btn-gerar-qrcode" data-bs-toggle="modal" data-bs-target="#TFAModal" data-qrcode-user-id="<?=$user->id?>">
        Configurar a Autenticação de Dois Fatores (2FA)
      </button>
    </div>
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
        <h5 class="modal-title" id="TFAModalLabel">Configuração da Autenticação em Dois Fatores (2FA)</h5>
      </div>
      <div class="modal-body">
        <p>Basta escanear o QrCode com o aplicativo de 2FA de sua preferência.</p>
        <div class="row">
          <div class="col-sm text-center">
            <div id="imagemQrCode"></div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm text-center">
            <button type="button" class="btn btn-outline-secondary btn-gerar-qrcode" data-qrcode-user-id="<?=$user->id?>" data-qrcode-novo="1">
              Gerar novo QrCode
            </button>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>



