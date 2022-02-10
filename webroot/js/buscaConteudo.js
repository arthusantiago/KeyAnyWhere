/**
 *
 * @param
 * @param 
 * @author Arthu Vinicius <contato@avds.eti.br>
 */
function gerenciadorBusca(recurso, metodo, dados)
{
	//padronizar o caminho no formato: /controller/action/parametro

	// Montando a URL completa
	var urlCompleta = window.location.origin + recurso;

	opcoes = {
		metodo: metodo,
		headers: {
	      'Content-Type': 'application/json'
	    },
	};

	const request = consomeApi(urlCompleta, opcoes, dados);

	request.then(dados => dados.json())
	.then(dadosRetornados => {
		manipulaHtml(dadosRetornados.htmlProc);
	});

}

/**
 *
 * @param
 * @param 
 * @author Arthu Vinicius <contato@avds.eti.br>
 */
async function consomeApi(url, opcoes, dados)
{
	optionFetch = {
		method: opcoes.metodo,
	    headers: opcoes.headers,
	};

	if (dados){
		optionFetch.body = JSON.stringify(dados);
	}

  	return await fetch(url, optionFetch);
}

/**
 *
 * @param
 * @param 
 * @author Arthu Vinicius <contato@avds.eti.br>
 */
function manipulaHtml(conteudo)
{
	document.getElementById('corpoConteudo').innerHTML = conteudo;	
}

// --------------------------------------------------------------------

/**
 *
 * @param
 * @param 
 * @author Arthu Vinicius <contato@avds.eti.br>
 */
function buscaConteudo(caminho)
{
	//padronizar o caminho no formato: /controller/action/parametro

	// Montando a URL completa
	var urlParaBusca = window.location.origin + caminho;

	atualizandoURL(urlParaBusca);

	fetch(urlParaBusca)
	.then(response => response.text())
	.then(function(dadosRetornados){
		document.getElementById('corpoConteudo').innerHTML = dadosRetornados;
	})
	.catch(function(error){
	  console.log("Aconteceu um erro na busca: " + error.message);
	});
}

function atualizandoURL(url)
{
	history.pushState({page: 1}, null, url);
}
