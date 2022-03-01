<div class="row">
    <div class="col-sm-auto">
        <span class="titulo">Subcategoria <?=$subcategoria->nome?></span>
    </div>
</div>

<br>

<?= $this->Form->create($subcategoria); ?>
    <?php $this->Form->secure(['nome']); ?>
    <div class="row">
        <div class="col-sm-4">
          <label for="nome">Nome da subcategoria</label>
          <input type="text" class="form-control inputs" id="nome" name="nome" value="<?=$subcategoria->nome?>">
        </div>
        <div class="col-sm-4">
          <label for="nome">Pertence a categoria</label>
          <input type="text" class="form-control inputs" placeholder="<?=$subcategoria->categoria->nome?>" readonly>
        </div>        
    </div>
    <div class="row">
        <div class="col-sm text-end">
            <?= $this->element('Diversos/btnSalvar')?>
        </div>
    </div>
<?= $this->Form->end(['data-type' => 'hidden']);?>
