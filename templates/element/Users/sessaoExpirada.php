<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="modal fade" id="sessaoExpiradaModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">A sessão expirou</h5>
            </div>
            <div class="modal-body">
                <p>O seu tempo de sessão acabou. Você precisa logar novamente.</p>
            </div>
            <div class="modal-footer">
                <a class="btn btn-primary" href="<?= $this->Url->build(['controller' => 'users', 'action' => 'login']) ?>" role="button">Entrar</a>
            </div>
        </div>
    </div>
</div>