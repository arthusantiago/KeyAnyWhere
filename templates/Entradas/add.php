<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Categoria[]|\Cake\Collection\CollectionInterface $categorias
 * @var \App\Model\Entity\Entrada $entrada
 */
?>
<div class="row">
    <div class="col-sm-auto mb-3">
        <span class="titulo">Nova Entrada</span>
    </div>
</div>

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
    <div class="col-sm mb-3">
      <label for="titulo" class="form-label">Título</label>
      <input type="text" class="form-control inputs" name="titulo" id="titulo" maxlength="87" required>
    </div>
    <div class="col-sm mb-3">
      <label for="username" class="form-label">Usuário</label>
      <input type="text" class="form-control inputs" name="username" id="username" maxlength="88" required>
    </div>
  </div>
  <div class="row">
    <div class="col-sm mb-3">
      <label for="password" class="form-label">Senha</label>
      <div class="input-group">
        <input type="password" class="form-control inputs pwd" name="password" id="password" maxlength="88" autocomplete="new-password" required>
        <div class="btn-group">
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
      <input type="url" class="form-control inputs" name="link" id="link" maxlength="400" placeholder="http://seuLink.com.br">
    </div>
  </div>
  <div class="row">
    <div class="col-sm-6 mb-3">
      <label for="categoria_id" class="form-label">Categoria</label>
      <select class="form-select inputs" id="categoria_id" name="categoria_id" required>
        <option value="" disabled selected>Escolha a Categoria</option>
        <?php foreach ($categorias as $categoria):?>
          <option value="<?=$categoria->id?>"><?=h($categoria->nomeDescrip())?></option>
        <?php endforeach ?>
      </select> 
    </div>
  </div>
  <div class="row">
    <div class="col-sm mb-3">
      <label for="anotacoes" class="form-label">Anotações</label>
      <textarea class="form-control" id="anotacoes" name="anotacoes" maxlength="1000" rows="2"></textarea>
    </div>
  </div>
  <div class="row">
    <div class="col-sm mb-3 text-end">
      <?= $this->element('Diversos/btnSalvar')?>
    </div>
  </div>
<?= $this->Form->end();?>
<?= $this->element('geradorSenha')?>
