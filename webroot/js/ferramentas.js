/*
 * Configuração do modal e cronometro do tempo de sessão
 */
if (document.getElementById('sessionTimeout')) {
    let tempoSessao = document.getElementById('sessionTimeout').getAttribute('content') * 60;
    let sessaoExpiradaModal = new bootstrap.Modal(document.getElementById('sessaoExpiradaModal'));
    let timer = new easytimer.Timer();
    timer.start({countdown: true, startValues: {seconds: tempoSessao}});
    timer.addEventListener('targetAchieved', () => {sessaoExpiradaModal.show()});
}

/**
 * Exibe o conteúdo de um input=password .
 *
 * @param Event event Evento que está acionando a function (manipulado)
 */
function exibirConteudoInput(event)
{
    let element = document.getElementById(event.target.getAttribute('data-revelar'));
    let typeInput = element.getAttribute('type') == 'password' ? 'text' : 'password';
    element.setAttribute('type', typeInput);
}
/* Aplicando o manipulador de evento no elemento HTML*/
document
	.querySelectorAll(".btn-revelar")
	.forEach(function (currentValue, currentIndex, listObj) {
		currentValue.addEventListener("click", exibirConteudoInput);
	});

/**
 * Adiciona ao botão de excluir registro o comportamento de abertura do modal de confirmação.
 *
 * @param Event event Evento que está acionando a function (manipulado)
 */
function modalExcluir(event)
{
	let button = event.target;
	if (button.tagName == 'I') {
		button = button.parentElement;
	}
	let id = button.getAttribute('data-excluir-id');
	let nomeModal = button.getAttribute('data-excluir-modal');

	//Exibindo o modal
    let bsModalExcluir = new bootstrap.Modal(document.getElementById(nomeModal)).show();

	// Preenchendo o form do modal com o id do registro que será excluído
	document
		.getElementById(nomeModal)
		.querySelector('#idExclusao')
		.setAttribute('value', id);
}
/* Aplicando o manipulador de evento no elemento HTML*/
document
	.querySelectorAll(".btnExcluir")
	.forEach(function (currentValue, currentIndex, listObj) {
		currentValue.addEventListener("click", modalExcluir);
	});


/**
 * Limpa o form de exclusão para evitar exclusões indesejadas.
 *
 * @param Event event Evento que está acionando a function (manipulado)
 * @return	void
 */
function limparModalExcluir(event) {
	let form = event.target.getAttribute('data-id-form');
	document
		.getElementById(form)
		.querySelector('#idExclusao')
		.setAttribute('value', '');
}
document
	.querySelector("#btnCancelarModalExcluir")
	.addEventListener("click", limparModalExcluir);
