/**
 * Função responsável por buscar as informações de user e senha e escrever na área de transferencia.
 *
 * @param elementHTML button O botão que foi clicado
 * @see https://developer.mozilla.org/en-US/docs/Web/API/Clipboard_API
 * @see https://web.dev/async-clipboard/
 */
async function buscaUserPass(button)
{
	if(button.getAttribute('data-clipboard-tipo') == 'pass'){
		endpoint = '/entradas/clipboard-pass/';
	}else{
		endpoint = '/entradas/clipboard-user/';
	}

	let urlParaBusca = window.location.origin + endpoint + button.getAttribute('data-clipboard-entrada-id');

	fetch(urlParaBusca)
	.then(response => response.text())
	.then(function(dadoRetornado){
		navigator.clipboard.writeText(dadoRetornado);
	})
	.catch(function(error){
		console.log("Aconteceu um erro na busca do usuário/senha: " + error.message);
	});
}

/**
 * Busca generica que envia ao servidor o JSON e espera receber um HTML de retorno.
 *
 * @param string idInputOrigemBusca ID do input onde foi digitado o texto da busca
 * @param string destinoHtmlRetorno ID do elemento HTML onde será inserido o retorno da request.
 * @param string urlParaBusca URL alvo da busca.
 * @param Objeto config Configurações adicionais para a execução da request.
 		'qtdCaracMin' (Obrigatório) Quantidade mínima de caracteres que o usuário precisa inserir no campo.
 		'tempoEspera' (Opcional) Tempo que a função deve esperar para executar a request pro servidor.
					  Informar em milissegundos. O padrão é 2000 ms (2 segundos)
 		'paramAdicional' : Você pode adicionar a requisição algum dado desejado em formato de JSON.
 */
var paraExecutar;
function buscaGenerica(idInputOrigemBusca, destinoHtmlRetorno, urlParaBusca, config)
{
	let inputOrigemBusca = document.getElementById(idInputOrigemBusca);
	if(inputOrigemBusca.value.length >= config.qtdCaracMin)
	{
		clearTimeout(paraExecutar);

		let chamadaAoServidor = function()
		{
			if (inputOrigemBusca.value.length >= config.qtdCaracMin) 
			{
				let dadosParaRequest = JSON.stringify({
					stringBusca: inputOrigemBusca.value,
					paramAdicional: config.paramAdicional
				});
				let csrfToken = document.getElementsByName('csrfToken').item([]).getAttribute('content');

				//executando a busca
				fetch(
					urlParaBusca,
					{
						'method': 'POST',
						'headers': {
							'Content-Type': 'text/json; charset=utf-8',
							'Accept': 'text/html, application/json',
							'X-CSRF-Token': csrfToken
						},
						'body': dadosParaRequest,
					}
				)
				.then(response => response.text())
				.then(function(dadoRetornado){
					document.getElementById(destinoHtmlRetorno).innerHTML = dadoRetornado;
				})
				.catch(function(error){
					alert('Ocorreu um erro na busca');
					console.log("Erro ao executar a busca: " + error.message);
				});
			}
		};

		if(config.tempoEspera)
		{
			paraExecutar = setTimeout(chamadaAoServidor, config.tempoEspera);
		}else{
			paraExecutar = setTimeout(chamadaAoServidor, 2000);
		}
		document.getElementById(destinoHtmlRetorno).innerHTML = "<li><a>Buscando...</a></li>";

	} else {
		document.getElementById(destinoHtmlRetorno).innerHTML = "<li><a>Digite no mínimo " + config.qtdCaracMin + " caracteres.</a></li>";
	}
}

/**
 * Função que limpa o resultado da busca.
 *
 * @param string idInputOrigemBusca ID do input onde foi digitado o texto da busca.
 * @param string idUlBusca ID do elemento UL que contem a listagem do resultado da busca.
 */
function removeResultadoBuscaGenerico(idInputOrigemBusca, idUlBusca) {
	setTimeout(
		function () {
			let temp_element = document.getElementById(idUlBusca);
			if (temp_element) {
				while (temp_element.childNodes[0] != null) {
					temp_element.removeChild(temp_element.childNodes[0]);
				}
			}
		},
		500
	);

	document.getElementById(idInputOrigemBusca).value = '';
}
