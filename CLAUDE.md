# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

KeyAnyWhere (KAW) is a self-hosted credential/password manager built on CakePHP 5. This is the **open-source community edition** — there is a closed-source paid edition (`keyanywhere-pago`) that shares the same architecture but adds licensing, an API, audit logging (Auditorias), and multi-tenancy. Do not assume paid-only concepts (Auditorias, Licencas, ApiCredenciais, API controllers) exist here — check `src/` before referencing them.

## Commands

Database: PostgreSQL only (no SQLite/MySQL support — the `default` and `test` datasources are hardcoded to `Cake\Database\Driver\Postgres`, and migrations use Postgres-specific SQL like `uuid_generate_v4()`).

```bash
composer install                    # install dependencies
cp config/.env.example config/.env  # then fill in DB_DEFAULT_*, DB_TEST_*, SECURITY_SALT, KEY_CRIPTOGRAFIC
cp config/app.example.php config/app.php
cp config/app_local.example.php config/app_local.php
bin/cake migrations migrate         # apply migrations to the `default` connection

composer test                       # run the full PHPUnit suite (needs DB_TEST_* pointed at a real Postgres db)
vendor/bin/phpunit tests/TestCase/Controller/UsersControllerTest.php          # single file
vendor/bin/phpunit --filter testLoginComCredenciaisECodigo2faValidosAutentica # single test

composer cs-check                   # phpcs (PSR-12 + CakePHP standard, must be 0 errors/0 warnings)
composer cs-fix                     # phpcbf autofix
composer stan                       # phpstan level 8 (see phpstan-baseline.neon for grandfathered errors)
composer check                      # test + cs-check + stan, run this before considering work done
```

CI (`.github/workflows/tests.yml`, `code-quality.yml`, `security.yml`) runs against a real `postgres:18` service container — there is no way to run the test suite against SQLite.

## Architecture

### Environment detection drives behavior, not `APP_ENV`

`App\Application::isTheExecutionEnvironment()` (consts `DESENVOLVIMENTO`/`PRODUCAO`/`SANDBOX`/`TESTE`) is the single source of truth for dev-vs-prod branching (DebugKit loading, CSRF cookie name, CSP headers, X-Frame-Options). It's computed from `Configure::read('debug')` plus whether `config/.env` exists and `SERVER_NAME` is set — **not** from an env var. `config/bootstrap.php` loads `.env` before `Configure` exists, so it duplicates the same file/SERVER_NAME check inline rather than calling this method.

`Application::isRunningUnitTests()` checks for `PHPUNIT_TESTSUITE`/`PHPUNIT_COMPOSER_INSTALL` constants and is used to disable the authentication redirect and skip CSRF checks during tests — integration tests never actually exercise real CSRF token validation.

### Middleware queue order matters

In `Application::middleware()`: ErrorHandler → Asset → Routing → BodyParser → Authentication → CSRF → HttpsEnforcer → SecurityHeadersKaw → **SessionsKawMiddleware** (must run after Authentication). `SessionsKawMiddleware` reads the current PHP session's row from the `sessions` table (populated by CakePHP's `DatabaseSession` handler) and stamps `user_id`/`user_agent` onto it once authenticated, or purges sessions older than a day when unauthenticated. In integration tests, that row doesn't exist unless you insert it yourself (see `AuthenticatedTestTrait`).

### Everything sensitive is encrypted at rest with libsodium, not hashed

`App\Criptografia\Criptografia` wraps `sodium_crypto_secretbox` using `env('KEY_CRIPTOGRAFIC')` as the key; each call generates a fresh nonce, so **encrypting the same plaintext twice produces different ciphertext** — this matters for tests and for auditing/diffing changed fields. It's used (via entity mutators/`*Descrip()` accessors) for `Entrada::titulo/username/password/link/anotacoes`, `Categoria::nome`, and `User::tfa_secret`. `User::password` is the only field hashed instead (bcrypt via `DefaultPasswordHasher`).

### Custom validation: `ValidatorKaw`

`App\Model\Custom\ValidatorKaw extends Validator` adds `checkXSS()`, used throughout `validationDefault()` on every Table. It runs `voku/anti-xss` against the field and, if it finds anything, fires a `C3-1` event through `GerenciadorEventos` — it relies on the `table`/`newRecord`/`data` keys Cake injects automatically when validating through `Table::newEntity()`/`patchEntity()`, so it cannot be tested via a bare `Validator::validate()` call (see `ValidatorKawTest` for the pattern: validate through a real Table).

### Event catalog (`App\Log`)

`GerenciadorEventos::notificarEvento(['evento' => 'C1-1', ...])` is the single entry point for security/audit logging across the app (failed logins, XSS attempts, blocked-IP hits, unauthorized access to root-only actions). Events are looked up by code (`C<category>-<n>`) in a static catalog in `GerenciadorEventos`, wrapped into an `Evento` value object, and logged via `Cake\Log\Log`. Some events (e.g. `C1-3`, three failed logins) are only fired as a side effect of other events through `EventosComplexos`, which replays the log table looking for a trigger threshold and then blocks the IP (`IpsBloqueadosTable`). The `'database'` log scope in `config/app.php` is a plain `FileLog`, not a DB-backed engine — despite the name, it does not write to the `logs` table in this edition.

### Auth & 2FA

Login (`UsersController::login`) requires email+password *and* a TOTP code (`pragmarx/google2fa`). `User::valida2fa()` skips real verification (accepts any code) whenever `Application::isTheExecutionEnvironment(DESENVOLVIMENTO)` is true — i.e. in local dev and in the test suite itself, since `config/.env` exists and `DEBUG=true` there. This means integration tests can't exercise "wrong 2FA code" through the full login flow; that's covered instead by a direct unit test on `User::valida2fa()` (`tests/TestCase/Model/Entity/UserTest.php`) that toggles `Configure::write('debug', ...)` to force each branch. First-run bootstrap (no users yet, or the last user has no 2FA) is handled by `UsersController::executarConfigInicial()` / `configInicial()` / `configInicialTfa()`, which is a distinct code path from normal login — `configInicialTfa()` is reachable without authentication (see `ACTIONS_SEM_AUTENTICACAO`), so it must `return` its early redirect when the system is already configured, or it will silently regenerate and persist a new 2FA secret for the last user on every hit (this was a real bug, fixed together with the same missing-`return`-before-`redirect()` pattern elsewhere in the same controller — `configInicial()`, `minhaConta()`, `finalizarSessao()`). `UsersController::SOMENTE_ROOT_ACESSA` gates index/add/edit/delete to `root` users only, redirecting non-root users to `Pages::home` and firing a `C2-1` event.

### Testing conventions

- Postgres sequences: any fixture with an explicit integer `id` must extend `tests/Fixture/AppFixture.php` (not `TestFixture` directly), which resyncs the sequence after insert — otherwise the next `save()` in that test collides on a duplicate key.
- `tests/TestCase/Controller/AuthenticatedTestTrait.php`'s `loginAsUser()`/`loginAsNonRootUser()` fake an authenticated session without going through a real login POST; it also inserts a matching `sessions` row (id `'cli'`, set in `tests/bootstrap.php`) so `SessionsKawMiddleware` doesn't choke on a missing row. The fake identity also carries a `tfa_secret` so actions that call `descripSecret2FA()` on the session identity directly (e.g. `UsersController::geraQrCode2fa()` for your own account) don't crash on a null value — a real session identity is loaded whole from the DB, this one isn't.
- Encrypted fields in fixtures must be pre-encrypted with `Criptografia::criptografar()` (see `EntradasFixture`, `CategoriasFixture`) — raw plaintext in a fixture will fail to decrypt when a test calls `*Descrip()`.
- `User::_accessible` guards `root`, `tfa_ativo` and `tfa_secret` — `$usersTable->newEntity([...])` silently drops them. Set them with direct property assignment on the returned entity instead (`$user->root = true;`) before saving, the same way the controller code itself does.
