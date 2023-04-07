<?php
    /*
    * Para mais opções de configuração, ler o arquivo ./vendor/cakephp/cakephp/src/View/Helper/PaginatorHelper.php
    */

    return [
        'first'        => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
        'last'         => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
        'nextActive'   => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
        'nextDisabled' => '<li class="page-item disabled"><a class="page-link" href="">{{text}}</a></li>',
        'prevActive'   => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
        'prevDisabled' => '<li class="page-item disabled"><a class="page-link" href="">{{text}}</a></li>',
        'number'       => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
        'current'      => '<li class="page-item active" aria-current="page"><span class="page-link">{{text}}</span></li>',
        'counterPages' => '<li class="page-item"><a class="page-link">{{page}} de {{pages}}</a></li>',
    ];
?>
