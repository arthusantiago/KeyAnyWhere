<?php foreach ($query as $categoria): ?>
<li>
    <a class="dropdown-item" href="<?=$this->Url->build(['controller' => 'Categorias', 'action' => 'listagemEntradas', $categoria->id]);?>">
        <?=h($categoria->nomeEncurtado())?>
    </a>
</li>
<?php endforeach; ?>