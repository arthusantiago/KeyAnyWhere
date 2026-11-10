<?php

/**
 * @var \App\View\AppView $this
 */
?>
<?php echo $this->Html->script('geradorSenha.js', ['block' => 'script-last-load']); ?>

<div class="modal" tabindex="-1" id="modalGeradorSenha">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= __('Gerador de senha') ?></h5>
      </div>
      <div class="modal-body">
        <div class="container-fluid">
          <div class="row mb-4">
            <div class="col-sm">
              <label for="tamanho" class="form-label" id="labelTamanho"><?= __('Quantidade de dígitos') ?></label>
              <input type="range" class="form-range" name="tamanho" id="tamanho" list="markers" min="12" max="20" step="1" value="14" required>
              <datalist id="markers">
                <option value="12" label="12"></option>
                <option value="13" label="13"></option>
                <option value="14" label="14"></option>
                <option value="15" label="15"></option>
                <option value="16" label="16"></option>
                <option value="17" label="17"></option>
                <option value="18" label="18"></option>
                <option value="19" label="19"></option>
                <option value="20" label="20"></option>
              </datalist>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-5">
              <label for="tamanho" class="form-label"><?= __('Senha gerada') ?></label>
              <div class="input-group">
                <input type="text" class="form-control inputs" name="senhaGerada" id="senhaGerada" readonly>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary botoes" data-bs-dismiss="modal" id="btn-aplica-senha"><?= __('Utilizar esta senha') ?></button>
        <button type="button" class="btn btn-secondary botoes" data-bs-dismiss="modal"><?= __('Fechar') ?></button>
      </div>
    </div>
  </div>
</div>