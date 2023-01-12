
<div class="row">
  <div class="col-sm-auto">
    <span class="titulo">Entrada</span>
  </div>
</div>

<br/>

<?= $this->Form->create($entrada, ['url' => ['action' => 'edit', $entrada->id]])?>
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
      <input type="text" class="form-control inputs" name="titulo" id="titulo" value="<?=$entrada->titulo?>" required>
    </div>
    <div class="col-sm">
      <label for="username" class="form-label">Usuário</label>
      <input type="text" class="form-control inputs" name="username" id="username" value="<?=$entrada->usernameDescrip()?>" required>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm">
      <label for="password" class="form-label">Senha</label>
      <div class="input-group">
        <input type="password" class="form-control inputs pwd" name="password" id="senhaEntrada" autocomplete="new-password" 
          value="<?=$entrada->passwordDescrip()?>" required>
        <div class="btn-group">
          <button type="button" class="btn btn-secondary" onclick="mostrarSenha()"><i class="fa fa-eye" aria-hidden="true"></i></button>
          <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="visually-hidden">Opções</span>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Gerador de senha</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-sm">
      <label for="link" class="form-label">Link</label>
      <input type="url" class="form-control inputs" name="link" id="link" value="<?=$entrada->link?>">
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm-6">
      <label for="categoria_id" class="form-label">Categoria</label>
      <select class="form-select inputs" id="categoria_id" name="categoria_id" required>
        <option value="" disabled selected>Escolha a Categoria</option>
        <?php foreach ($categorias as $categoria):?>
          <option value="<?=$categoria->id?>" <?=$categoria->id == $entrada->categoria_id?'selected':''?>><?=$categoria->nome?></option>
        <?php endforeach ?>
      </select> 
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm">
      <label for="anotacoes" class="form-label">Anotações</label>
      <textarea class="form-control" id="anotacoes" name="anotacoes" rows="4"><?=$entrada->anotacoes?></textarea>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm text-end">
      <?= $this->element('Diversos/btnSalvar')?>
    </div>
  </div>
<?= $this->Form->end(['data-type' => 'hidden']);?>

<!-- usado para marcar em qual categoria o usuário está -->
<input type="hidden" id="id-categoria-selecionada" value="<?=$entrada->categoria_id?>">
<script type="text/javascript">
  document.dispatchEvent(new Event("categoria"));
</script>
