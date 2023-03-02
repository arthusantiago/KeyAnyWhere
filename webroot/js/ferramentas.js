function exibirConteudoInput(campo = 'password')
{
    if(document.getElementById(campo).getAttribute('type') == 'password'){
        valor = 'text';
    }else{
        valor = 'password';
    }

    document.getElementById(campo).setAttribute('type', valor);
}
