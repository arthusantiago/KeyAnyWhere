<?php
use Cake\Core\Exception\CakeException;

/**
  * Botão de exclusão de registro.
  * Para o botão funcionar o modal de exclusão precisa ser importado na mesma pagina. Exemplo:
  *     <?= $this->element('Diversos/modalExcluir', ['parametros' => ['controller' => 'Categoria', 'action' => 'apagar']])?>
  *
  * Exemplo de uso:
  *     <?=$this->element('Diversos/btnExcluir', ['idRegistro' => $entrada->id])?>
  *     É obrigatório informa o ID do registro
  *
  * @param string $idRegistro
**/

if (!$idRegistro) {
    throw new CakeException('O ID do registro para exclusão precisa ser informado');
}
?>
<a class="dropdown-item text-danger btnExcluir" href="#" data-excluir-id="<?=$idRegistro?>" data-excluir-modal="modalExcluirRegistro">
  <i class="bi bi-trash2 icone-opcao"></i>Excluir
</a>
