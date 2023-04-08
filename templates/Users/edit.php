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
            <label for="email">E-mail (login e rec. de senha)</label>
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
    <br>
    <div class="row">
    <div class="col-sm-auto">
        <span class="titulo">Segurança</span>
    </div>
    </div>
    <br>
    <div class="row">
        <div class="col-sm-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#TFAModal" 
            onclick="obterQrCode2FA('imagemQrCode', '<?=$this->Url->build(['controller' => 'Users', 'action' => 'geraQrCode2fa', '?' => ['idUser' => $user->id]], ['fullBase' => true])?>')">
            Configurar a Autenticação de Dois Fatores (2FA)
        </button>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-sm text-end">
            <?= $this->element('Diversos/btnSalvar') ?>
        </div>
    </div>
<?=$this->Form->end(['data-type' => 'hidden']); ?>

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
                '<?=$this->Url->build(['controller' => 'Users', 'action' => 'geraQrCode2fa', '?' => ['novoQrCode' => '1', 'idUser' => $user->id]], ['fullBase' => true])?>',
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
