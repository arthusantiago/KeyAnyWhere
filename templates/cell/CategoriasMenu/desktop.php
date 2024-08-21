<ul class="nav flex-column">
	<?php foreach ($query as $categoria):?>
		<li class="nav-item categorias" data-categoriaId="<?=$categoria->id?>">
			<i class="bi bi-caret-right" id="icone-categoria"></i>
			<a class="nav-link d-inline-block" href="<?=$this->Url->build(['controller' => 'Categorias', 'action' => 'listagemEntradas', $categoria->id]);?>"  
			title="<?=h($categoria->nomeDescrip())?>">
				<?=h($categoria->nomeEncurtado())?>
			</a>
		</li>
	<?php endforeach;?>
</ul>
