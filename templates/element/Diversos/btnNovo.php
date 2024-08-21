<?php 
/**
  * Botão padrão para a criação de alguma coisa.
  *
  * Exemplo de chamada:
  * 
  * <?= $this->element('Diversos/btnNovo', ['parametros' => ['controller' => 'Categoria', 'texto' => 'Categoria']])?>
  *
  * Se a action não for informada nos parametros, a action 'add' é chamada. 
  * 
  * Especificando a action: 
  * <?=$this->element('Diversos/btnNovo', ['parametros' => [
  * 	'controller' => 'Categoria',
  * 	'action' => 'add',
  * 	'texto' => 'Categoria'	
  * ]])?>
  * 
  * O indece 'texto' contem o texto que aparece dentro do botão. Se o texto não for informado, e exibido o texto 'Novo'
  *
  * @param array $parametros com chave/valor dos atributos/valores que serão usadas na geração da URL do botão:
  * 	Parâmetros esperados:
  *		'controller' : Controlador
  *		'action' : Metodo do controlador.
  * 						 Se não for informado, é chamada a action 'add'
  * 	'texto' : Texto localizado dentro do botão
  *
  *   Parâmetros opcionais:
  *   	'atributo' : Contendo um array chave/valor dos atributos que devem ser inseridos no elemento HTML
  *   
  * 		Exemplo: 
  * 		<?= $this->element('Diversos/btnNovo',[ 
  * 			'parametros' => [
  * 				'controller' => 'Categoria',
  * 				'atributo' => ['style' => 'background-color: red']
  * 			]
  * 		])?>
  *
**/ 

//Gerando a URL
$url = null;

if (isset($parametros['action']) == true && empty($parametros['action']) == false)
{
	$url = $this->Url->build([
    'controller' => $parametros['controller'],
    'action' => $parametros['action'],
    'prefix' => false
  ]);
}else{
	$url = $this->Url->build([
		"controller" => $parametros['controller'],
		"action" => 'add',
    'prefix' => false
	]);
}

// Gerando a string dos atributos
$atributoHTML = ""; 

if (isset($parametros['atributo']) == true && empty($parametros['atributo']) == false)
{
	foreach ($parametros['atributo'] as $atributo => $valor)
	{
		$atributoHTML .= ' ' . "$atributo='$valor'" . ' ';
	}
}

//Gerando o texto do botão
$texto = 'Novo';

if (isset($parametros['texto']) == true && empty($parametros['texto']) == false)
{
	$texto = $parametros['texto'];
}

?>

<a
  href="<?=$url?>"
	class="btn btn-sm btn-outline-success botoes"
	role="button"
  <?=$atributoHTML?>
>
	<i class="bi bi-plus-lg"></i><?=$texto?>
</a>