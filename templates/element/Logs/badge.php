<?php
/**
 * @var \App\View\AppView $this
 * @var array $param
 */
/*
    [
        'severidade' => 1, // Numero inteiro que represente o Nivel de Severidade
        'exibicao' => 'Danger' // Informação que será exibida dentro do badge
    ]
*/
$stringHTML = '';

switch ($param['severidade']) {
    case 0: // EMERGENCY
        $stringHTML = "<span class='badge rounded-pill bg-danger'>{$param['exibicao']}</span>";
        break;
    case 1: // ALERT
        $stringHTML = "<span class='badge rounded-pill' id='badge-alert'>{$param['exibicao']}</span>";
        break;
    case 2: // CRITICAL
        $stringHTML = "<span class='badge rounded-pill bg-warning'>{$param['exibicao']}</span>";
        break;
    case 3: // ERROR
        $stringHTML = "<span class='badge rounded-pill' id='badge-error'>{$param['exibicao']}</span>";
        break;
    case 4: // WARNING
        $stringHTML = "<span class='badge rounded-pill bg-secondary'>{$param['exibicao']}</span>";
        break;
    case 5: // NOTICE
        $stringHTML = "<span class='badge rounded-pill bg-info'>{$param['exibicao']}</span>";
        break;
    default:
        $stringHTML = "<span class='badge rounded-pill bg-light text-dark'>{$param['exibicao']}</span>";;
        break;
}

echo $stringHTML;
