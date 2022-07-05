/**
 * Este arquivo reuni todos os scripts de busca do sistema
 */


/**
 * Busca as subcategorias pertencentes a categoria selecionada em um elemento HTML <select>
 *
 * @param <select> selectOrigem Elemento HTML de origem da chamada.
 * @param string selectDestino ID do Elemento HTML onde será inserido o retorno da chamada.
 * @author Arthu Vinicius <contato@avds.eti.br>
 */
function buscaSubcategorias(selectOrigem, selectDestino)
{
	let urlParaBusca = '/subcategorias/assinc-subcategorias/' + selectOrigem.value;

	fetch(urlParaBusca)
	.then(response => response.text())
	.then(function(dadosRetornados){
        document.getElementById(selectDestino).innerHTML = dadosRetornados;
	})
	.catch(function(error){
        alert("Aconteceu um erro ao buscar as subcategorias");
	});
}