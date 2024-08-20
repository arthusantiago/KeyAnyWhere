<?php
use Cake\Core\Exception\CakeException;

/**
  * Modal da confirmação da exclusão. O modal faz um POST para o backend com o ID para exclusão
  *
  * Exemplo de chamada:
  *    <?= $this->element('Diversos/modalExcluir', ['parametros' => ['controller' => 'Categoria', 'action' => 'apagar']])?>
  *    Se 'action' não for informada será usado um valor default.
  *
  * @param array $parametros
**/

if (!$parametros['controller']) {
    throw new CakeException('O controller precisa ser informado');
}

$url = $this->Url->build([
    'controller' => $parametros['controller'],
    'action' => $parametros['action'] ?? 'delete',
]);
?>
<div class="modal fade" id="modalExcluirRegistro" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <br>
        <p>Você tem certeza dessa ação?</p>
        <?= $this->Form->create(null, ['url' => $url, 'id' => 'formModalExcluirRegistro']); ?>
          <input type="hidden" id="idExclusao" name="id" value="">
          <input type="hidden" name="_csrfToken" autocomplete="off" value="<?=$csrfToken?>">
        <?= $this->Form->end(['data-type' => 'hidden']);?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary botoes" id="btnCancelarModalExcluir" data-id-form="formModalExcluirRegistro" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-sm btn-danger botoes" form="formModalExcluirRegistro">Confirmar</button>
      </div>
    </div>
  </div>
</div>
