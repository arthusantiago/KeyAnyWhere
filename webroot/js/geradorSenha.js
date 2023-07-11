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

function generatePassword(inputTamanho, inputDestino) {
    let len = document.getElementById(inputTamanho).value;
    let password = "";

    for (let i = 0; i < len; i++) {
        let x = generateX();
        password += x;
    }

    document.getElementById(inputDestino).value = password;
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

function aplicaSenha(idInputPass, idInputSenhaGerada) {
    document.getElementById(idInputPass).value = document.getElementById(idInputSenhaGerada).value;
    document.getElementById(idInputPass).dispatchEvent(new Event('change'));
}

function atualizaLabel(idLabel, idInputTamanho) {
    document.getElementById(idLabel).innerHTML = 'Tamanho: ' + document.getElementById(idInputTamanho).value + ' caracteres';
}
