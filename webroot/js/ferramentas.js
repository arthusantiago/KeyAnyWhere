/*
 * Configuração do modal e cronometro do tempo de sessão
 */
if (document.getElementById('sessionTimeout')) {
    let tempoSessao = document.getElementById('sessionTimeout').getAttribute('content') * 60;
    let myModal = new bootstrap.Modal(document.getElementById('sessaoExpiradaModal'));
    let timer = new easytimer.Timer();
    timer.start({countdown: true, startValues: {seconds: tempoSessao}});
    document.getElementById('timerSessao').innerHTML = timer.getTimeValues().toString();
    timer.addEventListener('secondsUpdated', function (e) {
        document.getElementById('timerSessao').innerHTML = timer.getTimeValues().toString();
    });
    timer.addEventListener('targetAchieved', () => {myModal.show()});
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
