/*
 * Configuração do modal e cronometro do tempo de sessão
 */
let tempoSessao = document.getElementById('sessionTimeout').getAttribute('content') * 60;
let myModal = new bootstrap.Modal(document.getElementById('sessaoExpiradaModal'));
let timer = new easytimer.Timer();
timer.start({countdown: true, startValues: {seconds: tempoSessao}});
document.getElementById('timerSessao').innerHTML = timer.getTimeValues().toString();
timer.addEventListener('secondsUpdated', function (e) {
    document.getElementById('timerSessao').innerHTML = timer.getTimeValues().toString();
});
timer.addEventListener('targetAchieved', () => {myModal.show()});

/**
 * Exibe o conteúdo de um input=password .
 *
 * @param	string	campo	Default: 'password'
 * @return	void
 */
function exibirConteudoInput(campo = 'password')
{
    if(document.getElementById(campo).getAttribute('type') == 'password'){
        valor = 'text';
    }else{
        valor = 'password';
    }

    document.getElementById(campo).setAttribute('type', valor);
}
