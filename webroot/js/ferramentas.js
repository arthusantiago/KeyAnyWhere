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

/**
 * Verifica se no campo de link foi inserido o protocolo 'http://' ou 'https://'. 
 * Se não localizar a função insere na string digitada.
 *
 * @param	string	campo	Default: 'link'
 * @return	void
 */
function veriProtoHttp(campo = 'link')
{
    var expression = /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)/gi;
    var regex = new RegExp(expression);
    if(
        !regex.test(document.getElementById(campo).value) &&
        document.getElementById(campo).value.length > 0 // o usuário quer preencher o campo
    ){
        document.getElementById(campo).value = 'http://' + document.getElementById(campo).value
    }
}
