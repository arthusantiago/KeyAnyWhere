<?php
/**
 * Plugins carregados pela aplicação, no formato `NomeDoPlugin => [opções]` (ver
 * https://book.cakephp.org/5/en/plugins.html#loading-plugins-via-configuration-array).
 *
 * Os plugins do core (DebugKit, Migrations, Bake...) são carregados em `Application::bootstrap()`.
 * Plugins próprios de uma instalação entram em `config/plugins_local.php`, que não é versionado aqui e
 * devolve um array no mesmo formato. Ex.:
 *
 * ```php
 * <?php
 * return ['MeuPlugin' => []];
 * ```
 */
$plugins = [];

if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'plugins_local.php')) {
    $plugins = array_merge($plugins, (array)require __DIR__ . DIRECTORY_SEPARATOR . 'plugins_local.php');
}

return $plugins;
