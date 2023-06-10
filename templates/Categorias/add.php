<div class="row">
    <div class="col-sm-auto">
        <span class="titulo">Nova Categoria</span>
    </div>
</div>

<br>

<?= $this->Form->create($categoria); ?>
  <?php $this->Form->secure(['nome']); ?>
  <div class="row">
    <div class="col-sm-4">
      <label for="nome">Nome da categoria</label>
      <input type="text" class="form-control inputs" id="nome" name="nome" maxlength="88">
    </div>
  </div>
  <br>
  <div class="d-grid gap-2 d-md-flex justify-content-md-end">
    <button class="btn btn-primary botoes" type="submit">Salvar</button>
  </div>
<?= $this->Form->end(['data-type' => 'hidden']); ?>
