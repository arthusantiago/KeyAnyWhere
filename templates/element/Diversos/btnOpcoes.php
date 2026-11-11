<?php
/**
 * Botão que concentra as opções não centrais do sistema, como configuração, logout, minha conta e etc.
 *
 * Um plugin pode acrescentar itens por configuração (no bootstrap() dele):
 * - `Kaw.Menu.configuracoes`: itens da seção "Configurações", que só o root vê;
 * - `Kaw.Menu.outros`: itens da seção "Outros".
 * Cada item: `['url' => array|string, 'icone' => 'bi-...', 'rotulo' => 'Texto', 'dominio' => 'default',
 * 'somenteRoot' => false]`. O rótulo é traduzido com `__d($dominio, $rotulo)`.
 * `Kaw.Configuracoes.regionalEditavel = false` esconde "Configurações gerais" (ver ConfiguracoesController).
 *
 * @var \App\View\AppView $this
 * @var object $userLogado
 **/

use Cake\Core\Configure;

$itensDoMenu = function (string $secao) use ($userLogado): array {
    $itens = (array)Configure::read('Kaw.Menu.' . $secao, []);

    return array_filter($itens, fn($item): bool => is_array($item) && (empty($item['somenteRoot']) || $userLogado->root));
};
$itensConfiguracoes = $itensDoMenu('configuracoes');
$itensOutros = $itensDoMenu('outros');
$itemDoMenu = function (array $item): string {
    return sprintf(
        '<li><a class="dropdown-item" href="%s"><i class="bi %s"></i>%s</a></li>',
        h($this->Url->build($item['url'] ?? '/')),
        h($item['icone'] ?? 'bi-dot'),
        h(__d($item['dominio'] ?? 'default', (string)($item['rotulo'] ?? ''))),
    );
};
?>
<div class="btn-group dropdown">
    <button type="button" class="btn btn-outline-light" data-bs-toggle="dropdown">
        <i class="bi bi-three-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item" href="<?= $this->Url->build(['controller' => 'Pages', 'action' => 'home']) ?>">
                <i class="bi bi-house"></i><?=__('Home')?>
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="<?= $this->Url->build(['controller' => 'Categorias', 'action' => 'index']); ?>">
                <i class="bi bi-list-ol"></i><?=__('Categorias')?>
            </a>
        </li>
        <li id="btn-menu-opcao-busca">
            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalBusca">
                <i class="bi bi-search"></i><?=__('Busca')?>
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'minhaConta']) ?>">
                <i class="bi bi-person"></i><?=__('Minha Conta')?>
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="<?= $this->Url->build(['controller' => 'Auditorias', 'action' => 'index']) ?>">
                <i class="bi bi-clock-history"></i><?=__('Auditoria')?>
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="<?= $this->Url->build(['controller' => 'EventosSeguranca', 'action' => 'index']) ?>">
                <i class="bi bi-shield-exclamation"></i><?=__('Eventos de segurança')?>
            </a>
        </li>
    <?php if ($userLogado->root): ?>
        <li><h6 class="dropdown-header"><?=__('Configurações')?></h6></li>
        <?php if (Configure::read('Kaw.Configuracoes.regionalEditavel', true)): ?>
        <li>
            <a class="dropdown-item" href="<?= $this->Url->build(['controller' => 'Configuracoes', 'action' => 'geral']) ?>">
                <i class="bi bi-gear"></i><?=__('Configurações gerais')?>
            </a>
        </li>
        <?php endif; ?>
        <?php foreach ($itensConfiguracoes as $item): ?>
        <?= $itemDoMenu($item) ?>
        <?php endforeach; ?>
        <li>
            <a class="dropdown-item" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'index']) ?>">
                <i class="bi bi-people"></i><?=__('Usuários')?>
            </a>
        </li>
    <?php endif; ?>
    <?php if ($itensOutros): ?>
        <li><h6 class="dropdown-header"><?=__('Outros')?></h6></li>
        <?php foreach ($itensOutros as $item): ?>
        <?= $itemDoMenu($item) ?>
        <?php endforeach; ?>
    <?php endif; ?>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'logout']) ?>">
                <i class="bi bi-box-arrow-right"></i><?=__('Sair')?>
            </a>
        </li>
    </ul>
</div>