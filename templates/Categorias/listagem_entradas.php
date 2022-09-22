<table class="table table-borderless table-striped table-hover">
    <thead>
        <tr class="text-center titulo-coluna-tabela">
            <th>Titulo</th>
            <th>URL</th>
            <th>Opções</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($entradas as $entrada): ?>
            <tr class="text-center">
                <td><?= h($entrada->titulo) ?></td>
                <td>
                	<a href="<?=$entrada->link?>" target="_blank" class="text-decoration-none" title="<?=$entrada->link?>">
                		<?=$entrada->linkEncurtado(30)?>
                	</a>
                </td>
                <td>
            		<button type="button" class="btn btn-sm btn-secondary" data-clipboard-text="<?=$entrada->username?>" title="Copiar usuário">
	                    <i class="bi bi-person-fill"></i>
	                </button>
 					<button type="button" class="btn btn-sm btn-secondary" data-clipboard-text="<?=$entrada->password?>" title="Copiar senha">
	                    <i class="bi bi-key-fill"></i>
	                </button>
	                <a class="btn btn-sm btn-secondary" role="button"  href="<?=$this->Url->build(['controller' => 'Entradas', 'action' => 'edit', $entrada->id])?>" title="Editar entrada">
	                	<i class="bi bi-pencil-fill"></i>
	                </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- usado para marcar em qual categoria o usuário está -->
<input type="hidden" id="id-categoria-selecionada" value="<?=$categoria_id?>">
<script type="text/javascript">
  document.dispatchEvent(new Event("categoria"));
</script>