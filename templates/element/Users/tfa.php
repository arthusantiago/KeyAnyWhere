<div class="modal fade" id="TFAModal" tabindex="-1" aria-labelledby="TFAModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="TFAModalLabel">Configuração da Autenticação em Dois Fatores (2FA)</h5>
      </div>
      <div class="modal-body">
        <p>Escaneie o QrCode com o aplicativo de 2FA de sua preferência.</p>
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
