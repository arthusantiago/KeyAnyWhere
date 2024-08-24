<?php
/**
 * Modal do mecanismo de busca do sistema
 */
?>
<div class="modal fade" id="modalBusca" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Buscar uma entrada</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <br>
        <?=$this->element('buscaInput', ['inserirResultadoBusca' => 'ul-busca-responsivo'])?>
        <div class="div-resultado-busca">
          <ul class="ul-busca" id="ul-busca-responsivo"></ul>
        </div>
        <br>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary botoes" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>
