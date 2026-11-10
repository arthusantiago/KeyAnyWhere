<label for="password"><?=__('Senha')?></label>
<div class="input-group">
    <input type="password" id="password" name="password" class="form-control inputs pwd" autocomplete="new-password" minlength="12" placeholder="************">
    <div class="btn-group">
        <button type="button" class="btn btn-secondary btn-revelar" data-revelar="password">
            <i class="bi bi-eye" aria-hidden="true" title="Revelar" data-revelar="password"></i>
        </button>
    </div>
    <?= $this->element('Diversos/feedbackPasswordInsecure') ?>
</div>
<br>
<p><?=__('A senha criada precisa atender todos os requisitos abaixo')?>:</p>
<ul>
    <li><?=__('No mínimo 12 caracteres')?></li>
    <li><?=__('Precisa conter letras, números e caracteres especiais como')?>: !@#$%^&*()-_=+</li>
</ul>
