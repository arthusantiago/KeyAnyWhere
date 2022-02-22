<ul class="nav flex-column">
	<?php foreach ($query as $categoria):?>
		<li class="nav-item categorias" data-categoriaId="<?=$categoria->id?>">
			<i class="bi bi-caret-right" style=" color: white;"></i>
			<a class="nav-link d-inline-block" href="<?=$this->Url->build(['controller' => 'Categorias', 'action' => 'listagemEntradas', $categoria->id]);?>"  title="<?=$categoria->nome?>">
				<?= $categoria->nomeEncurtado(35)?>
			</a>
		</li>
	<?php endforeach;?>
</ul>