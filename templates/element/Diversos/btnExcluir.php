<?php
use Cake\Core\Exception\CakeException;

/**
  * Botão de exclusão de registro.
  * Para o botão funcionar o modal de exclusão precisa ser importado na mesma pagina. Exemplo:
  *     <?= $this->element('Diversos/modalExcluir', ['parametros' => ['controller' => 'Categoria', 'action' => 'apagar']])?>
  *
  * Exemplo de uso:
  *    <?=$this->element('Diversos/btnExcluir', ['idRegistro' => $entrada->id, 'texto' => 'Finalizar', 'tipo' => 'button'])?>
  *    'tipo':
  *      'dropdown-item' (Default) Será exibido como uma opção em um dropdown
  *      'button' Será exibido como um botão normal
  *
  * @param string $idRegistro (Obrigatório) ID do registro que será excluído
  * @param string $texto (Opcional) Texto do botão
  * @param string $tipo (Opcional) Tipo do botão
**/

if (!$idRegistro) {
    throw new CakeException('O ID do registro para exclusão precisa ser informado');
}

//Texto do botão
$texto = $texto ?? 'Excluir';

// Tipo do botão
$tipo = $tipo ?? 'dropdown-item';
$atributosHtmlBotao = '';

switch ($tipo) {
  case 'dropdown-item':
    $atributosHtmlBotao = 'class="dropdown-item text-danger btnExcluir" ';
    break;

  case 'button':
    $atributosHtmlBotao = 'class="btn btn-sm btn-outline-danger botoes btnExcluir" role="button"';
    break;
}
?>
<a <?=$atributosHtmlBotao?> href="#" data-excluir-id="<?=$idRegistro?>" data-excluir-modal="modalExcluirRegistro">
  <i class="bi bi-trash2 icone-opcao"></i><?=$texto?>
</a>
