<label for="password">Senha</label>
<div class="input-group">
    <input type="password" id="password" name="password" class="form-control inputs pwd" autocomplete="new-password" minlength="12" placeholder="************">
    <div class="btn-group">
        <button type="button" class="btn btn-secondary btn-revelar" data-revelar="password">
            <i class="bi bi-eye" aria-hidden="true" title="Revelar" data-revelar="password"></i>
        </button>
    </div>
    <div id="feedbackPasswordInsecure" class="invalid-feedback">
        <strong>Esta senha é insegura</strong><br> A localizamos em vazamentos de dados e pode facilmente ser hackeada.
    </div>
</div>
<br>
<p>
    <strong>Observação</strong><br> Escolha uma senha que atenda a todos os requisitos abaixo:
</p>
<ul>
    <li>No mínimo 12 caracteres</li>
    <li>Contendo letras, números e caracteres especiais como: !@#$%^&*()-_=+</li>
    <li>Não deve ser utilizada em outro lugar</li>
    <li>Não anote, você deve memorizar a senha</li>
</ul>
