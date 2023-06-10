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
