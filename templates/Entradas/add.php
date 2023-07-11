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
      <input type="text" class="form-control inputs" name="titulo" id="titulo" maxlength="87" required>
    </div>
    <div class="col-sm">
      <label for="username" class="form-label">Usuário</label>
      <input type="text" class="form-control inputs" name="username" id="username" maxlength="88" required>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm">
      <label for="password" class="form-label">Senha</label>
      <div class="input-group">
        <input type="password" class="form-control inputs pwd" name="password" id="password" maxlength="88" autocomplete="new-password"
          onchange="estaComprometida('password')" required>
        <div class="btn-group">
          <button type="button" class="btn btn-secondary" onclick="exibirConteudoInput()"><i class="fa fa-eye" aria-hidden="true"></i></button>
          <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="visually-hidden">Opções</span>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalGeradorSenha" onclick="generatePassword('tamanho', 'senhaGerada')">Gerador de senha</a></li>
          </ul>
        </div>
        <div id="feedbackPasswordInsecure" class="invalid-feedback">
          <strong>Esta senha é insegura</strong>. A localizamos em vazamentos de dados e pode facilmente ser descoberta.
        </div>
      </div>
    </div>
    <div class="col-sm">
      <label for="link" class="form-label">Link</label>
      <input type="url" class="form-control inputs" name="link" id="link" maxlength="210" placeholder="http://seuLink.com.br">
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm-6">
      <label for="categoria_id" class="form-label">Categoria</label>
      <select class="form-select inputs" id="categoria_id" name="categoria_id" required>
        <option value="" disabled selected>Escolha a Categoria</option>
        <?php foreach ($categorias as $categoria):?>
          <option value="<?=$categoria->id?>"><?=$categoria->nomeDescrip()?></option>
        <?php endforeach ?>
      </select> 
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm">
      <label for="anotacoes" class="form-label">Anotações</label>
      <textarea class="form-control" id="anotacoes" name="anotacoes" maxlength="1000" rows="2"></textarea>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-sm text-end">
      <?= $this->element('Diversos/btnSalvar')?>
    </div>
  </div>

<?= $this->Form->end();?>
<?= $this->element('geradorSenha')?>
