<div class="row">
    <div class="col-sm-auto">
        <span class="titulo">Nova Entrada</span>
    </div>
</div>

<br>

<?= $this->Form->create($entrada)?>
  <?php $this->Form->secure([
    'titulo',
    'username',
    'password',
    'link',
    'categoria_id',
    'anotacoes'
  ]);?>
  <div class="row">
    <div class="col-sm">
      <label for="titulo" class="form-label">Título</label>
      <input type="text" class="form-control inputs" name="titulo" id="titulo" required>
    </div>
    <div class="col-sm">
      <label for="username" class="form-label">Usuário</label>
      <input type="text" class="form-control inputs" name="username" id="username" required>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm">
      <label for="password" class="form-label">Senha</label>
      <div class="input-group">
        <input type="password" class="form-control inputs" name="password" id="password"  autocomplete="new-password" required>
        <button class="btn btn-secondary">
          <?= $this->Html->image('generate-password.svg', ['width' => '24', 'height' => '24'])?>
        </button>
      </div>
    </div>
    <div class="col-sm">
      <label for="link" class="form-label">Link</label>
      <input type="url" class="form-control inputs" name="link" id="link">
    </div>    
  </div>
  <br>
  <div class="row">
    <div class="col-sm-6">
      <label for="categoria_id" class="form-label">Categorias</label>
      <select class="form-select inputs" id="categoria_id" name="categoria_id" required>
        <option value="" disabled>Escolha a Categoria</option>
        <?php foreach ($categorias as $categoria):?>
          <option value="<?=$categoria->id?>"> <?=$categoria->nome?></option>
        <?php endforeach ?>
      </select> 
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm">
      <label for="anotacoes" class="form-label">Anotações</label>
      <textarea class="form-control" id="anotacoes" name="anotacoes" rows="2"></textarea>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm text-end">
      <?= $this->element('Diversos/btnSalvar')?>
    </div>
  </div>

<?= $this->Form->end(['data-type' => 'hidden']);?>
