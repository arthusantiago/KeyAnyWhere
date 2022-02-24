<?php 
/**
  * Botão padrão para a edição de alguma coisa.
  *
  * Exemplo de chamada:
  * 
  * <?= $this->element('Diversos/btnEditar', ['parametros' => ['controller' => 'Categoria', 'texto' => 'Categoria']])?>
  *
  * Se a action não for informada nos parametros, a action 'edit' é chamada. 
  * 
  * Especificando a action: 
  * <?=$this->element('Diversos/btnEditar', ['parametros' => [
  * 	'controller' => 'Categoria',
  * 	'action' => 'editDiferente',
  *   'id' => '3',
  * 	'texto' => 'Categoria'	
  * ]])?>
  * 
  * O indece 'texto' contem o texto que aparece dentro do botão, Se o texto não for informado, e exibido o texto 'Editar'
  *
  * @param array $parametros com chave/valor dos atributos/valores que serão usadas na geração da URL do botão:
  * 	Parâmetros esperados:
  *		'controller' : Controlador
  *		'action' : Metodo do controlador. Se não for informado, é chamada a action 'edit'
  *   'id' : ID do registro que será editado
  * 	'texto' : Texto localizado dentro do botão
  *
  *   Parâmetros opcionais:
  *   	'atributo' : Contendo um array chave/valor dos atributos que devem ser inseridos no elemento HTML
  *   
  * 		Exemplo: 
  * 		<?= $this->element('Diversos/btnEditar',[ 
  * 			'parametros' => [
  * 				'controller' => 'Categoria',
  *         'edit' => 3,
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
    'prefix' => false,
    $parametros['id'],
  ]);
}else{
	$url = $this->Url->build([
		"controller" => $parametros['controller'],
		"action" => 'edit',
    'prefix' => false,
    $parametros['id'],
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
$texto = 'Editar';

if (isset($parametros['texto']) == true && empty($parametros['texto']) == false)
{
	$texto = $parametros['texto'];
}
?>

<a  
	href="<?=$url?>"
	class="btn btn-sm btn-outline-secondary botoes"
	role="button"
  <?=$atributoHTML?>
>
	<i class="bi bi-pencil icone-opcao"></i><?=$texto?>
</a>