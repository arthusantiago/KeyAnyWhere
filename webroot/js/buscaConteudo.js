/**
 * Função responsavel por fazer todas as request ao servidor.
 *
 * @param url Pra onde será feita a requisição
 * @param parametros Objeto que contem todas as configurações para executar a request/response.
 * 		Tem a mesma função do 'options' do fetch()
 * @see https://developer.mozilla.org/en-US/docs/Web/API/fetch
 * @global
 */
var factoryRequest = async function (url, parametros)
{
	let headersDefault = {
		'Content-Type': 'application/json; charset=utf-8',
		'Accept': 'application/json',
	};

	// Setando valores default
	if (parametros == undefined) {
		parametros = {'headers': headersDefault};
	} else {
		if (parametros.headers != undefined) {
			if (parametros.headers['Content-Type'] == undefined) {
				parametros.headers['Content-Type'] = headersDefault['Content-Type'];
			}

			if (parametros.headers['Accept'] == undefined) {
				parametros.headers['Accept'] = headersDefault['Accept'];
			}
		} else {
			parametros.headers = headersDefault;
		}
	}

	parametros.headers['X-CSRF-Token'] = document.getElementsByName('csrfToken').item([]).getAttribute('content');

	return await fetch (
		url,
		{
			'method': parametros['method'] ?? 'POST',
			'headers': parametros.headers,
			'credentials': "same-origin",
			'body': parametros['body'] ?? null,
		}
	);
}

/**
 * Função responsável por buscar as informações de user e senha e escrever na área de transferencia.
 *
 * @param elementHTML button O botão que foi clicado
 * @see https://developer.mozilla.org/en-US/docs/Web/API/Clipboard_API
 * @see https://web.dev/async-clipboard/
 */
async function buscaUserPass(button)
{
	let body = {
		'type' : button.getAttribute('data-clipboard-tipo'),
		'id' : button.getAttribute('data-clipboard-entrada-id')
	};
	let urlParaBusca = window.location.origin + '/entradas/clipboard/';

	factoryRequest(urlParaBusca, {'body' : JSON.stringify(body)})
	.then((response) => {
		if (!response.ok) {
			throw new Error(response.status + ' - '+ response.statusText);
		}
		return  response.json();
	})
	.then(function(dadoRetornado){
		navigator.clipboard.writeText(dadoRetornado.data);
	})
	.catch(function(error){
		let msgErro = 'Ocorreu um erro \n\n' + error.message;
		alert(msgErro);
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
let paraExecutar;
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

				factoryRequest(
					urlParaBusca,
					{
						'headers' : {'Accept': 'text/html'},
						'body' : dadosParaRequest,
					}
				)
				.then((response) => {
					if (!response.ok) {
						throw new Error(response.status + '-'+ response.statusText);
					}
					return response.text();
				})
				.then(function(dadoRetornado){
					document.getElementById(destinoHtmlRetorno).innerHTML = dadoRetornado;
				})
				.catch(function(error){
					let msgErro = 'Ocorreu um erro na requisição ao servidor: ' + error.message;
					alert(msgErro);
					console.error(msgErro);
				});
			}
		};

		if(config.tempoEspera){
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

/**
 * Busca o QrCode do 2FA.
 *
 * @param integer idUser ID do usuario que será manipulado
 * @param boolean novoQrCode Se deve ser gerado um novo QrCode.
 */
function obterQrCode2FA(idUser, novoQrCode = false)
{
	let body = JSON.stringify({
		idUser: idUser,
		novoQrCode: novoQrCode
	});

	let parametros = 		{
		'headers': {'Accept': 'text/html'},
		'body': body,
	};

	let urlParaBusca = window.location.origin + '/users/geraQrCode2fa/';

	factoryRequest(urlParaBusca, parametros)
	.then((response) => {
		if (!response.ok) {
			throw new Error(response.status + ' - '+ response.statusText);
		}
		return response.text();
	})
	.then(function (dadoRetornado) {
		document.getElementById('imagemQrCode').innerHTML = dadoRetornado;
	})
	.catch(function (error) {
		alert('Ocorreu um erro \n\n' + error.message);
	});
}

/**
 * Faz uma chamada para o server verificar se a senha é insegura.
 *
 * @param string inputName ID do input=text|password onde a senha foi inserida
 * @return void
 */
function estaComprometida(inputName)
{
	let body = JSON.stringify({"password": document.getElementById(inputName).value});
	let url = window.location.origin + '/entradas/senha-insegura/';

	factoryRequest(url, {'body' : body})
	.then(function(response){
		if (!response.ok) {
			throw new Error(response.status + '-'+ response.statusText);
		}
		return response.json();
	})
	.then(function (dadoRetornado) {
		let strClass = document.getElementById(inputName).getAttribute('class');
		if (dadoRetornado.localizado) {
			strClass = strClass + ' is-invalid';
		} else {
			strClass = strClass.replace(/is-invalid/g, "");
		}
		document.getElementById(inputName).setAttribute('class', strClass);
	})
	.catch(function (error) {
		let msgErro = 'Ocorreu um erro na requisição ao servidor: ' + error.message;
		alert(msgErro);
		console.error(msgErro);
	});
}
