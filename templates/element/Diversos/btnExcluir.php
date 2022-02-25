<?php 
/**
  * Botão padrão para excluir alguma coisa.
  *
  * Exemplo de chamada:
  * 
  * <?= $this->element('Diversos/btnExcluir', ['parametros' => ['controller' => 'Categoria', 'id' => 5]])?>
  *
  * Se a action não for informada nos parametros, a action 'delete' é chamada. 
  * 
  * Especificando a action: 
  * <?=$this->element('Diversos/btnExcluir', ['parametros' => [
  * 	'controller' => 'Categoria',
  * 	'action' => 'delete',
  *   'id' => 5,
  * ]])?>
  *
  * @param array $parametros com chave/valor dos atributos/valores que serão usadas na geração da URL do botão:
  * 	Parâmetros esperados:
  *		'controller' : Controlador
  *		'action' : Metodo do controlador
  *   'id' : ID do registro que será excluído
**/ 

//Gerando a URL
$urlExclusao = null;

$parametrosURL = [
  'controller' => $parametros['controller'],
  'action' => 'delete', // action padrão
  'prefix' => false,
  $parametros['id']
]; 

if (isset($parametros['action']) == true && empty($parametros['action']) == false)
{
  $parametrosURL['action'] = $parametros['action'];
}

$urlExclusao = $this->Url->build($parametrosURL);

//Gerando ID único para o modal
$idModal = "modal-exclusao-{$parametros['id']}";

//Gerando ID único para o formulario de exclusão
$idForm = "form-exclusao-{$parametros['id']}";
?>

<!-- Button trigger modal -->
<button type="button" class="btn btn-sm btn-outline-danger botoes" data-bs-toggle="modal" data-bs-target="#<?=$idModal?>">
  <i class="bi bi-trash2 icone-opcao"></i>Excluir
</button>

<!-- Modal -->
<div class="modal fade" id="<?=$idModal?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Realmente deseja excluir esse registro?</p>
        <p class="bg-danger text-white">Todos os outros registro vinculados a ele também serão excluídos</p>
        <?= $this->Form->create(null, ['url' => $urlExclusao, 'id' => $idForm]); ?>
          <?= $this->Form->hidden('id', ['value' => $parametros['id']]); ?>
        <?= $this->Form->end(['data-type' => 'hidden']);?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary botoes" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-sm btn-danger botoes" form="<?=$idForm?>">Excluir</button>
      </div>
    </div>
  </div>
</div>
