<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Migrations\TestSuite\Migrator;

/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/config/bootstrap.php';

// Mutação em paralelo (Infection, `threads` no infection.json5): cada processo recebe o TEST_TOKEN
// (1, 2, ...) e usa recursos próprios, para os testes de um processo não disputarem as fixtures, o
// cache e o SQLite do DebugKit com os de outro. Sem TEST_TOKEN (o phpunit direto, a cobertura), os de
// sempre.
$token = getenv('TEST_TOKEN');
$token = is_string($token) && preg_match('/^\d+$/D', $token) === 1 ? $token : null;

if (empty($_SERVER['HTTP_HOST']) && !Configure::read('App.fullBaseUrl')) {
    Configure::write('App.fullBaseUrl', 'http://localhost');
}

// DebugKit skips settings these connection config if PHP SAPI is CLI / PHPDBG.
// But since PagesControllerTest is run with debug enabled and DebugKit is loaded
// in application, without setting up these config DebugKit errors out.
ConnectionManager::setConfig('test_debug_kit', [
    'className' => 'Cake\Database\Connection',
    'driver' => 'Cake\Database\Driver\Sqlite',
    'database' => TMP . 'debug_kit' . ($token !== null ? '-' . $token : '') . '.sqlite',
    'encoding' => 'utf8',
    'cacheMetadata' => true,
    'quoteIdentifiers' => false,
]);

ConnectionManager::alias('test_debug_kit', 'debug_kit');

// Fixate sessionid early on, as php7.2+
// does not allow the sessionid to be set after stdout
// has been written to.
session_id('cli');

// Use migrations to build test database schema.
//
// Will rebuild the database if the migration state differs
// from the migration history in files.
//
// If you are not using CakePHP's migrations you can
// hook into your migration tool of choice here or
// load schema from a SQL dump file with
// use Cake\TestSuite\Fixture\SchemaLoader;
// (new SchemaLoader())->loadSqlFiles('./tests/schema.sql', 'test');

// Com TEST_TOKEN, o banco de teste do processo é `<banco>_<token>`, criado aqui na primeira vez; o
// Migrator abaixo monta o esquema dele. O cache em arquivo também ganha um prefixo por processo.
if ($token !== null) {
    $config = ConnectionManager::getConfig('test');
    $banco = $config['database'] . '_' . $token;
    $pdo = new PDO(
        sprintf('pgsql:host=%s;port=%s;dbname=postgres', $config['host'], $config['port'] ?? 5432),
        $config['username'],
        $config['password'],
    );
    $consulta = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = ?');
    $consulta->execute([$banco]);
    if ($consulta->fetchColumn() === false) {
        $pdo->exec('CREATE DATABASE "' . $banco . '"');
    }
    unset($consulta, $pdo);
    ConnectionManager::drop('test');
    ConnectionManager::setConfig('test', ['database' => $banco] + $config);

    foreach (Cache::configured() as $nomeCache) {
        $configCache = Cache::getConfig($nomeCache);
        if (is_array($configCache)) {
            Cache::drop($nomeCache);
            Cache::setConfig($nomeCache, ['prefix' => ($configCache['prefix'] ?? '') . 't' . $token . '_'] + $configCache);
        }
    }
}

(new Migrator())->run();
