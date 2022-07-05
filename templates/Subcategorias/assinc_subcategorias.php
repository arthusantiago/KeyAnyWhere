<?php if($subcategorias->all()->isEmpty()): ?>
    <option disabled>Não há subcategorias</option>
<?php else: ?>
    <?php foreach($subcategorias as $subcategoria):?>
        <option value="<?=$subcategoria->id?>"><?=$subcategoria->nome?></option>
    <?php endforeach;?>
<?php endif; ?>
