function getUppercase() {
    let upperLetters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    return upperLetters[Math.floor(Math.random() * upperLetters.length)];
}

function getLowercase() {
    let lowerLetters = "abcdefghijklmnopqrstuvwxyz";
    return lowerLetters[Math.floor(Math.random() * lowerLetters.length)];
}

function getNumber() {
    let numbers = "0123456789";
    return numbers[Math.floor(Math.random() * numbers.length)];
}

function getSymbol() {
    let symbols = "!@#$%^&*()_+=-.,/~][{}:";
    return symbols[Math.floor(Math.random() * symbols.length)];
}

function generateX() {
    let xs = [];
    xs.push(getUppercase());
    xs.push(getLowercase());
    xs.push(getNumber());
    xs.push(getSymbol());
    if (xs.length === 0) return "";
    return xs[Math.floor(Math.random() * xs.length)];
}

/**
 * Gera a senha e exibe em um input
 *
 * @param Event event Evento que está acionando a function (manipulado)
 * @return	void
 */
function generatePassword(event) {
    let len = document.getElementById('tamanho').value;
    let password = "";

    for (let i = 0; i < len; i++) {
        password += generateX();
    }

    document.getElementById('senhaGerada').value = password;
}
/* Aplicando o manipulador de evento nos elementos HTML*/
document
    .getElementById('tamanho')
    .addEventListener("change", generatePassword);
document
    .getElementById('btn-gerador-senha')
    .addEventListener("click", generatePassword);


/**
 * Pega a senha gerada e atribui ao campo de password
 *
 * @param Event event Evento que está acionando a function (manipulado)
 * @return	void
 */
function aplicaSenha(event) {
    document.getElementById('password').value = document.getElementById('senhaGerada').value;
    document.getElementById('password').dispatchEvent(new Event('change'));
}
/* Aplicando o manipulador de evento no elemento HTML*/
document
    .getElementById('btn-aplica-senha')
    .addEventListener("click", aplicaSenha);


/**
 * Atualiza a label com a quantidade de caracteres da senha gerada
 *
 * @param Event event Evento que está acionando a function (manipulado)
 * @return	void
 */
function atualizaLabel(event) {
    document.getElementById('labelTamanho').innerHTML = 'Tamanho: ' + document.getElementById('tamanho').value + ' caracteres';
}
/* Aplicando o manipulador de evento no elemento HTML*/
document
    .getElementById('tamanho')
    .addEventListener("change", atualizaLabel);
