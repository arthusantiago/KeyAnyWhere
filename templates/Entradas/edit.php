<!-- usado para marcar em qual categoria o usuário está -->
<input type="hidden" id="id-categoria-selecionada" value="<?=$entrada->categoria_id?>">
<?=$this->Html->script('minified/categorias.min.js', ['block' => 'script-first-load']);?>

<div class="row">
  <div class="col-sm-auto mb-3">
    <span class="titulo">Entrada</span>
  </div>
</div>

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
    <div class="col-sm mb-3">
      <label for="titulo" class="form-label">Título</label>
      <input type="text" class="form-control inputs" name="titulo" id="titulo" value="<?=h($entrada->tituloDescrip())?>" maxlength="87" required>
    </div>
    <div class="col-sm mb-3">
      <label for="username" class="form-label">Usuário</label>
      <div class="input-group">
        <input type="password" class="form-control inputs" name="username" id="username" value="<?=h($entrada->usernameDescrip())?>" maxlength="88" required>
        <div class="btn-group">
          <button type="button" class="btn btn-secondary btn-clipboard" data-clipboard-entrada-id="<?=$entrada->id?>" data-clipboard-tipo="user">
            <i class="bi bi-clipboard" data-clipboard-entrada-id="<?=$entrada->id?>" data-clipboard-tipo="user"></i>
	        </button>
          <button type="button" class="btn btn-secondary btn-revelar" data-revelar="username">
            <i class="bi bi-eye" aria-hidden="true" title="Revelar" data-revelar="username"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-sm mb-3">
      <label for="password" class="form-label">Senha</label>
      <div class="input-group">
        <input type="password" class="form-control inputs pwd" name="password" id="password" autocomplete="new-password" maxlength="88" 
          value="<?=h($entrada->passwordDescrip())?>" required>
        <div class="btn-group">
          <button type="button" class="btn btn-secondary btn-clipboard" data-clipboard-entrada-id="<?=$entrada->id?>" data-clipboard-tipo="password">
            <i class="bi bi-clipboard" data-clipboard-entrada-id="<?=$entrada->id?>" data-clipboard-tipo="password"></i>
	        </button>
          <button type="button" class="btn btn-secondary btn-revelar" data-revelar="password">
            <i class="bi bi-eye" aria-hidden="true" title="Revelar" data-revelar="password"></i>
          </button>
          <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="visually-hidden">Opções</span>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" id="btn-gerador-senha" href="#" data-bs-toggle="modal" data-bs-target="#modalGeradorSenha">Gerador de senha</a></li>
          </ul>
        </div>
        <div id="feedbackPasswordInsecure" class="invalid-feedback">
          <strong>Esta senha é insegura</strong>. A localizamos em vazamentos de dados e pode facilmente ser hackeada.
        </div>
      </div>
    </div>
    <div class="col-sm mb-3">
      <label for="link" class="form-label">Link</label>
      <div class="input-group">
        <input type="url" class="form-control inputs" name="link" id="link" value="<?=h($entrada->linkDescrip())?>" maxlength="400" placeholder="http://seuLink.com.br">
        <div class="btn-group">
          <a <?=$entrada->linkDescrip() ? 'href="'.h($entrada->linkDescrip()).'" target="_blank"' : 'href="#"';?>>
            <button type="button" class="btn btn-secondary" title="Abrir link"><i class="bi bi-arrow-up-right-square"></i></button>
          </a>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-6 mb-3">
      <label for="categoria_id" class="form-label">Categoria</label>
      <select class="form-select inputs" id="categoria_id" name="categoria_id" required>
        <option value="" disabled selected>Escolha a Categoria</option>
        <?php foreach ($categorias as $categoria):?>
          <option value="<?=$categoria->id?>" <?=$categoria->id == $entrada->categoria_id?'selected':''?>><?=h($categoria->nomeDescrip())?></option>
        <?php endforeach ?>
      </select> 
    </div>
  </div>
  <div class="row">
    <div class="col-sm mb-3">
      <label for="anotacoes" class="form-label">Anotações</label>
      <textarea class="form-control" id="anotacoes" name="anotacoes" maxlength="1000" rows="4"><?=h($entrada->anotacoesDescrip())?></textarea>
    </div>
  </div>
  <div class="row">
    <div class="col-sm text-end">
      <?= $this->element('Diversos/btnSalvar')?>
    </div>
  </div>
<?= $this->Form->end(['data-type' => 'hidden']);?>

<?= $this->element('geradorSenha')?>
