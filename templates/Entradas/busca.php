<?php

$resultado = $resultado->toArray();
$qtdMaxCaracTitulo = 30;

if (empty($resultado)) {
    echo '<li><a>Não localizado</a></li>';
} else {
    foreach ($resultado as $entrada) {
        $url = $this->Url->build(['controller' => 'Entradas', 'action' => 'edit', $entrada->id]);
        $texto = $entrada->titulo;

        if (strlen($entrada->titulo) > $qtdMaxCaracTitulo) {
            $texto = substr($entrada->titulo, 0, 30) . ' (...)';
        }

        echo '<li><a href="' . $url . '">' . $texto . '</a></li>';
    }
}
