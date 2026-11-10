<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="modal fade" id="TFAModal" tabindex="-1" aria-labelledby="TFAModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="TFAModalLabel"><?=__('Configuração do segundo fator de autenticação')?> (2FA)</h5>
      </div>
      <div class="modal-body">
        <p><?=__('Escaneie o QrCode com o aplicativo de 2FA de sua preferência, como')?><br>
          <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en" target="_blank" class="text-decoration-none">Google Authenticator</a> e 
          <a href="https://play.google.com/store/apps/details?id=com.azure.authenticator&hl=en" target="_blank" class="text-decoration-none">Microsoft Authenticator</a>
            </p>
        <div class="row">
          <div class="col-sm text-center">
            <div id="imagemQrCode"></div>
          </div>
        </div>
        <?= $this->element('Users/dadosManuais2fa') ?>
        <div class="row">
          <div class="col-sm text-center">
            <button type="button" class="btn btn-outline-secondary btn-gerar-qrcode botoes" data-qrcode-user-id="<?=$user->id?>" data-qrcode-novo="1">
              <?=__('Gerar novo QrCode')?>
            </button>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary botoes" data-bs-dismiss="modal"><?=__('Fechar')?></button>
      </div>
    </div>
  </div>
</div>
