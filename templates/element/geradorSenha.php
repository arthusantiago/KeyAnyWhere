<?=$this->Html->script('geradorSenha.js', ['block' => 'script']);?>

<div class="modal" tabindex="-1" id="modalGeradorSenha">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Gerador de senha</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm">
              <label for="tamanho" class="form-label" id="labelTamanho">Tamanho: 14 caracteres</label>
              <div class="input-group">
                <input type="range" class="form-range" name="tamanho" id="tamanho" min="12" max="20" step="1" value="14" 
                    onchange="atualizaLabel('labelTamanho', 'tamanho'); generatePassword('tamanho', 'senhaGerada')"  required>
              </div>
            </div>
          </div>
          <br><br>
          <div class="row">
            <div class="col-sm-5">
              <label for="tamanho" class="form-label">Senha gerada</label>
              <div class="input-group">
                <input type="text" class="form-control inputs" name="senhaGerada" id="senhaGerada" placeholder="senha forte aqui " readonly>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="aplicaSenha('password', 'senhaGerada')">Aplicar senha</button>
      </div>
    </div>
  </div>
</div>
