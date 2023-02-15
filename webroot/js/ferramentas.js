function mostrarSenha()
{
    if(document.getElementById('senhaEntrada').getAttribute('type') == 'password'){
        valor = 'text';
    }else{
        valor = 'password';
    }

    document.getElementById('senhaEntrada').setAttribute('type', valor);
}