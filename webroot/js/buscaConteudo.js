/**
 * Função responsavel por fazer todas as request ao servidor.
 *
 * @param url Pra onde será feita a requisição
 * @param parametros Objeto que contem todas as configurações para executar a request/response.
 * 		Tem a mesma função do 'options' do fetch()
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
 * Função responsável por buscar as informações de user/senha e escrever na área de transferencia.
 *
 * @param Event event Evento que está acionando a function (manipulado)
 */
async function buscaUserPass(event)
{
	let body = {
		'type' : event.target.getAttribute('data-clipboard-tipo'),
		'id' : event.target.getAttribute('data-clipboard-entrada-id')
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
/* Aplicando o manipulador de evento no elemento HTML*/
document
	.querySelectorAll(".btn-clipboard")
	.forEach(function (currentValue, currentIndex, listObj) {
		currentValue.addEventListener("click", buscaUserPass);
	});


/**
 * Busca generica que envia ao servidor o JSON e espera receber um HTML de retorno.
 *
 * A função ira acessar três atributos no elemento HTML que acionou o manipulador:
 * 'data-busca-inserir-resultado' : ID do elemento HTML onde será inserido o html de retorno do servidor
 * 'data-busca-url' : URL para onde será disparada a request
 * 'data-busca-config' : Configurações adicionais para a execução da request.
 * 		'qtdCaracMin' (Obrigatório) Quantidade mínima de caracteres que o usuário precisa inserir no campo.
 * 		'tempoEspera' (Opcional) Tempo que a função deve esperar para executar a request pro servidor.
 *				  Informar em milissegundos. O padrão é 2000 ms (2 segundos)
 *		'paramAdicional' : Você pode adicionar a requisição algum dado desejado em formato de JSON.
 * @param Event event Evento que está acionando a function (manipulado)
 */
let paraExecutar;
function buscaGenerica(event)
{
	let inputOrigemBusca = event.target;
	let destinoHtmlRetorno = inputOrigemBusca.getAttribute('data-busca-inserir-resultado');
	let urlParaBusca = inputOrigemBusca.getAttribute('data-busca-url');
	let config = JSON.parse(inputOrigemBusca.getAttribute('data-busca-config'));

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
						throw new Error(response.status + ' - '+ response.statusText);
					}
					return response.text();
				})
				.then(function(dadoRetornado){
					document.getElementById(destinoHtmlRetorno).innerHTML = dadoRetornado;
				})
				.catch(function(error){
					alert('Ocorreu um erro \n\n' + error.message);
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
/* Aplicando o manipulador de evento no elemento HTML*/
let element = document.getElementById('buscaEntrada');
if (element) {
	element.addEventListener("input", buscaGenerica);
}


/**
 * Função que limpa o resultado da busca.
 *
 * @param Event event Evento que está acionando a function (manipulado)
 */
function removeResultadoBuscaGenerico(event) {
	let inputOrigemBusca = event.target;
	setTimeout(
		function () {
			let temp_element = document.getElementById(inputOrigemBusca.getAttribute('data-busca-inserir-resultado'));
			if (temp_element) {
				while (temp_element.childNodes[0] != null) {
					temp_element.removeChild(temp_element.childNodes[0]);
				}
			}
		},
		500
	);

	inputOrigemBusca.value = '';
}
/* Aplicando o manipulador de evento no elemento HTML*/
element = document.getElementById('buscaEntrada');
if (element) {
	element.addEventListener("input", buscaGenerica);
}


/**
 * Busca o QrCode do 2FA.
 *
 * @param Event event Evento que está acionando a function (manipulado)
 */
function obterQrCode2FA(event)
{
	let body = JSON.stringify({
		idUser: event.target.getAttribute('data-qrcode-user-id'),
		novoQrCode: event.target.getAttribute('data-qrcode-user-id') ? true : false
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
/* Aplicando o manipulador de evento no elemento HTML*/
document
	.querySelectorAll(".btn-gerar-qrcode")
	.forEach(function (currentValue, currentIndex, listObj) {
		currentValue.addEventListener("click", obterQrCode2FA);
	});


/**
 * Faz uma chamada ao servidor para verificar se a senha é insegura.
 *
 * @param Event event Evento que está acionando a function (manipulado)
 * @return void
 */
function estaComprometida(event, inputName)
{
	let input = event.target;

	if (!input.value) {
		return;
	}

	let body = JSON.stringify({"password" : input.value});
	let url = window.location.origin + '/entradas/senha-insegura/';

	factoryRequest(url, {'body':body})
	.then(function(response){
		if (!response.ok) {
			throw new Error(response.status + ' - '+ response.statusText);
		}
		return response.json();
	})
	.then(function (dadoRetornado) {
		let strClass = input.getAttribute('class');
		if (dadoRetornado.localizado) {
			strClass = strClass + ' is-invalid';
		} else {
			strClass = strClass.replace(/is-invalid/g, "");
		}
		input.setAttribute('class', strClass);
	})
	.catch(function (error) {
		alert('Ocorreu um erro \n\n' + error.message);
	});
}
/* Aplicando o manipulador de evento no elemento HTML*/
document
	.querySelectorAll(".pwd")
	.forEach(function (currentValue, currentIndex, listObj) {
		currentValue.addEventListener("change", estaComprometida);
	});
