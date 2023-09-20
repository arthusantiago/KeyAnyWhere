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
                <td><?= h($entrada->tituloDescrip()) ?></td>
                <td>
                	<a href="<?=$entrada->linkDescrip()?>" target="_blank" class="text-decoration-none" title="<?=$entrada->linkDescrip()?>">
                		<?=$entrada->linkEncurtado(50)?>
                	</a>
                </td>
                <td>
            		<button type="button" class="btn btn-sm btn-outline-secondary botoes" title="Copiar usuário"
                        data-clipboard-entrada-id="<?=$entrada->id?>" data-clipboard-tipo="user" onclick="buscaUserPass(this)">
	                    <i class="bi bi-person-fill"></i> Usuário
	                </button>
 					<button type="button" class="btn btn-sm btn-outline-secondary botoes" title="Copiar senha"
                        data-clipboard-entrada-id="<?=$entrada->id?>" data-clipboard-tipo="pass" onclick="buscaUserPass(this)">
	                    <i class="bi bi-key-fill"></i> Senha
	                </button>
	                <a class="btn btn-sm btn-outline-secondary botoes" role="button"  href="<?=$this->Url->build(['controller' => 'Entradas', 'action' => 'edit', $entrada->id])?>" title="Editar entrada">
	                	<i class="bi bi-pencil-fill"></i> Editar
	                </a>
                    <?= $this->element('Diversos/btnExcluir', ['parametros' => ['controller' => 'Entradas', 'id' => $entrada->id, 'texto' => '']])?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<br>
<?= $this->element('paginacao');?>

<!-- usado para marcar em qual categoria o usuário está -->
<input type="hidden" id="id-categoria-selecionada" value="<?=$categoria_id?>">
<script type="text/javascript">
    document.dispatchEvent(new Event("categoria"));
</script>