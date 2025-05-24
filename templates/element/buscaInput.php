<?php
/**
 * @var \App\View\AppView $this
 * @var string $inserirResultadoBusca
 */
use Cake\Core\Exception\CakeException;

/**
 * Input do mecanismo de busca do sistema
*/

if (!$inserirResultadoBusca) {
    throw new CakeException('Não foi informado o local onde deve ser inserido o resultado da busca');
}

$url = $this->Url->build(['controller' => 'Entradas', 'action' => 'busca']);
?>

<input type="search" class="form-control input-busca inputs" id="buscaEntrada" placeholder="Ex: banco..." data-busca-url="<?=$url?>"
data-busca-inserir-resultado="<?=$inserirResultadoBusca?>">
