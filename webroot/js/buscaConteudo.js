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