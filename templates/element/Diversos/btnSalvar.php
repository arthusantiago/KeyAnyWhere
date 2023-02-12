<?php 
/**
  * Botão padrão para a salvar informações de um formulario.
  *
  * Exemplo de chamada:
  * 
  * <?= $this->element('Diversos/btnSalvar', ['parametros' => ['texto' => 'Categoria']])?>
  * 
  * O indece 'texto' contem o texto que aparece dentro do botão. Se o texto não for informado, e exibido o texto 'Salvar'
  *
  * @param array $parametros com chave/valor dos atributos/valores que serão usadas na geração da URL do botão:
  * 	Parâmetros:
  * 	'texto' : Texto localizado dentro do botão
  *   'atributo' : Contendo um array chave/valor dos atributos que devem ser inseridos no elemento HTML
  * 	Exemplo: <?= $this->element('Diversos/btnSalvar', [ 'parametros' => ['atributo' => ['style' => 'background-color: red']]])?>
**/ 

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
$texto = 'Salvar';

if (isset($parametros['texto']) == true && empty($parametros['texto']) == false)
{
	$texto = $parametros['texto'];
}

?>

<button type="submit" class="btn btn-sm btn-outline-primary botoes" <?=$atributoHTML?>>
  <?=$texto?>
</button>