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
 * Busca genérica que envia ao servidor o JSON e espera receber um HTML de retorno.
 * Atributos esperados no elemento HTML que acionou o manipulador:
 * 'data-busca-inserir-resultado' : ID do elemento HTML onde será inserido o html de retorno do servidor
 * 'data-busca-url' : URL para onde será disparada a request
 *
 * @param Event event Evento que está acionando a function (manipulado)
 */
let paraExecutar;
function buscaGenerica(event)
{
	let inputOrigemBusca = event.target;
	let destinoHtmlRetorno = inputOrigemBusca.getAttribute('data-busca-inserir-resultado');
	let urlParaBusca = inputOrigemBusca.getAttribute('data-busca-url');
	let config = {
		'quantMinCaracter': 3,
		'tempoEspera': 2000 //milissegundos
	};

	if(inputOrigemBusca.value.length >= config.quantMinCaracter)
	{
		clearTimeout(paraExecutar);

		let chamadaAoServidor = function()
		{
			if (inputOrigemBusca.value.length >= config.quantMinCaracter)
			{
				let dadosParaRequest = JSON.stringify({
					stringBusca: inputOrigemBusca.value,
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

		paraExecutar = setTimeout(chamadaAoServidor, config.tempoEspera);
		document.getElementById(destinoHtmlRetorno).innerHTML = "<li><a>Buscando...</a></li>";
	} else {
		document.getElementById(destinoHtmlRetorno).innerHTML = "<li><a>Digite no mínimo " + config.quantMinCaracter + " caracteres</a></li>";
	}
}
/* Aplicando o manipulador de evento no elemento HTML*/
let inputsBuscas = document.querySelectorAll('.input-busca');
if (inputsBuscas) {
	inputsBuscas.forEach(function (currentValue, currentIndex, listObj) {
		currentValue.addEventListener('input', buscaGenerica);
	});
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
		300
	);

	inputOrigemBusca.value = '';
}
/* Aplicando o manipulador de evento no elemento HTML*/
let removerResultBusca = document.querySelectorAll('.input-busca');
if (removerResultBusca) {
	removerResultBusca.forEach(function (currentValue, currentIndex, listObj) {
		currentValue.addEventListener('blur', removeResultadoBuscaGenerico);
	});
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
		novoQrCode: event.target.getAttribute('data-qrcode-novo') ? true : false
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
		input.style.backgroundImage = 'none';
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

/**
 * Função responsável por buscar as informações de user/senha e escrever na área de transferencia.
 * Também exibe o tooltip da copia.
 *
 * @param Event event Evento que está acionando a function (manipulado)
 */
let buscaUserPass = async function (event) {
    let button = event.target;
    if (button.tagName == 'I') {
        button = button.parentElement;
    }

    let body = {
        'type': button.getAttribute('data-clipboard-tipo'),
        'id': button.getAttribute('data-clipboard-entrada-id')
    };

    let urlParaBusca = window.location.origin + '/entradas/clipboard/';

    try {
        // Verificando se clipboard está disponivel no navegador
        if (navigator.clipboard && navigator.clipboard.writeText)
		{
			// Passando a Promise diretamente ao clipboard para manter o contexto do user gesture
			// isso para executar em dispositivos da Apple
            await navigator.clipboard.writeText(
                await factoryRequest(urlParaBusca, {'body': JSON.stringify(body)})
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error(response.status + ' - ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(function(dadoRetornado) {
                        let tooltip = new bootstrap.Tooltip(
                            button,
                            {
                                'trigger': 'manual',
                                'title': body.type == 'user' ? 'Usuário copiado' : 'Senha copiada',
                            }
                        );
                        tooltip.show();

                        setTimeout(() => {
                            tooltip.hide();
                            setTimeout(() => {tooltip.dispose();}, 300);
                        }, 2000);

                        // Retorna o dado para o clipboard
                        return dadoRetornado.data;
                    })
            );
        } else {
			alert(
				'Funcionalidade não suportada'
				+ '\n\nSeu navegador não permite cópia automática para área de transferência.'
				+ '\n\nSolução: navegue até a entrada desejada e copie o conteúdo usando Ctrl+C.'
			);
        }
    } catch (error) {
		console.error(error);
		alert(
			'Erro ao copiar para área de transferência.'
			+ '\n\nSolução: navegue até a entrada desejada e copie o conteúdo usando Ctrl+C.'
			+ '\n\nDetalhes técnicos: ' + error.message
		);
    }
}
/* Aplicando o manipulador de evento no elemento HTML*/
document
	.querySelectorAll(".btn-clipboard")
	.forEach(function (currentValue, currentIndex, listObj) {
		currentValue.addEventListener("click", buscaUserPass);
	});
