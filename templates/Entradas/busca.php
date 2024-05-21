<?php

if (empty($resultado)) {
    echo '<li><a>Não localizado</a></li>';
} else {
    foreach ($resultado as $entrada) {
        $url = $this->Url->build(
            [
                'controller' => 'Entradas',
                'action' => 'edit',
                $entrada->id
            ],
            ['fullBase' => true]
        );
        $texto = $entrada->tituloDescrip();

        if (strlen($texto) > 30) {
            $texto = substr($texto, 0, 30) . ' (...)';
        }

        echo '<li><a href="' . $url . '">' . h($texto) . '</a></li>';
    }
}
