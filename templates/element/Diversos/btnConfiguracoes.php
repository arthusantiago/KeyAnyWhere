<?php
/**
  * Botão que concentra as opções não centrais do sistema, como configuração, logout, minha conta e etc.
  *
**/
?>
<div class="btn-group dropdown">
    <button type="button" class="btn btn-outline-light" data-bs-toggle="dropdown">
        <i class="bi bi-three-dots"></i>
    </button>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'index']) ?>">
                <i class="bi bi-gear icone-opcao"></i>Configurações
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'minhaConta']) ?>">
                <i class="bi bi-person icone-opcao"></i>Minha Conta
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'logout']) ?>">
                <i class="bi bi-box-arrow-right icone-opcao"></i>Sair
            </a>
        </li>
    </ul>
</div>
