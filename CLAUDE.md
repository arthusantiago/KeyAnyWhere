# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

**Local plugins:** if this checkout has plugins under `plugins/` (loaded by `config/plugins_local.php`), read the `CLAUDE.md` (and `README.md`) of each one before working: they describe what the installation adds on top of this core, and their rules apply there.

## Project overview

KeyAnyWhere (KAW) is a self-hosted credential/password manager built on CakePHP 5. This is the **open-source community edition** — there is a closed-source paid (hosted) edition; feature-wise both editions are meant to be identical. The paid edition adds only what depends on its hosting infrastructure (licensing, billing, an integration API, multi-tenancy). Do not assume paid-only concepts (Licencas, ApiCredenciais, API controllers) exist here — check `src/` before referencing them. **The core must only depend on public packages** — never on private ones. This repository is the single source of the core; the paid edition receives it by merge, so core changes are made here first.

## Commands

PHP 8.4+ (`composer.json` requires `>=8.4`; CI runs 8.4). Same minimum as the paid edition — PHP 8.1 has had no security fixes since 2025-12-31.

**`composer.json`/`composer.lock` are shared with the paid edition, so keep them identical there.** The paid edition receives this repo by `git merge`; it may differ only in `autoload`/`autoload-dev`/`scripts` (which don't enter the lock's `content-hash`), so dependency bumps here merge without conflict. That's why `firebase/php-jwt` is in `require` although the core doesn't use it: the paid edition's plugin needs it, and putting it here keeps the lock identical. Don't remove it as unused, and don't add private (non-Packagist) packages or `repositories`.

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
# phpstan.neon.dist / phpcs.xml.dist are the shared configs; a local phpstan.neon / phpcs.xml (git-ignored)
# overrides them, e.g. to add a plugin's paths (`includes: [phpstan.neon.dist]`).
composer check                      # test + cs-check + stan, run this before considering work done
```

### Code quality (`bin/qualidade`)

```bash
composer analise                    # PHPStan + PHPMD + Deptrac: only new errors fail; old debt lives in the baselines
composer md                         # PHPMD (phpmd.xml.dist): size, complexity, coupling (SRP); old violations in phpmd.baseline.xml
composer arquitetura                # Deptrac (deptrac.yaml): layer rules (DIP); old violations in deptrac.baseline.yaml
composer mutacao                    # coverage (test:cobertura) + Infection on infection.json5's directories, 1 process (~10 min; needs pcov/xdebug and the test DB)
composer mutacao:alteracoes         # Infection only on the lines changed since origin/main, escaped mutants in the output
composer qualidade                  # bin/qualidade --comparar: quality index 0-100 + gates + ratchet against qualidade-referencia.json
composer qualidade:referencia       # after improving a metric: write the new reference and commit it in the same PR
```

`bin/qualidade` (configured by `qualidade.json`; `--config=other.json` measures something else, e.g. a plugin's `src/`) scores coverage, mutation (MSI), types (files without PHPStan debt), complexity (PDepend: CC ≤ 10, NPath ≤ 200), cohesion (PHPMD) and architecture (Deptrac). Gates: no new PHPStan error, no new PHPMD violation, no new Deptrac violation. The ratchet (`--comparar`, CI job `Qualidade` in `code-quality.yml`) fails if any metric drops more than 0.5 point below `qualidade-referencia.json` or if the debt (PHPStan baseline + `@phpstan-ignore`, PHPMD baseline, Deptrac baseline, `@SuppressWarnings`) grows. Fix new issues instead of adding them to a baseline; when you fix old debt, regenerate the baseline (`composer stan:baseline`, `md:baseline`, `arquitetura:baseline`) and the reference. Deptrac layers: Controller/Console/Middleware → Table (incl. `Model\Custom`) → Entity, `Auditoria` used by Tables, `Nucleo` (`Criptografia`, `I18n`, `Log`) usable by every layer and depending on none of them, and `plugins/` may depend on the core but never the other way around. A new top-level namespace under `src/` needs a layer in `deptrac.yaml`.

CI (`.github/workflows/tests.yml`, `code-quality.yml`, `security.yml`) runs against a real `postgres:18` service container — there is no way to run the test suite against SQLite.

## Architecture

### Environment detection drives behavior, not `APP_ENV`

`App\Application::isTheExecutionEnvironment()` (consts `DESENVOLVIMENTO`/`PRODUCAO`/`SANDBOX`/`TESTE`) is the single source of truth for dev-vs-prod branching (DebugKit loading, CSRF cookie name, CSP headers, X-Frame-Options). It's computed from `Configure::read('debug')` plus whether `config/.env` exists and `SERVER_NAME` is set — **not** from an env var. `config/bootstrap.php` loads `.env` before `Configure` exists, so it duplicates the same file/SERVER_NAME check inline rather than calling this method.

`Application::isRunningUnitTests()` checks for `PHPUNIT_TESTSUITE`/`PHPUNIT_COMPOSER_INSTALL` constants and is used to disable the authentication redirect during tests. **CSRF is validated in tests, like in production**: only API routes skip it (`Application::pulaVerificacaoCsrf()`).

### Local plugins and API routes (extension points)

- `config/plugins.php` loads nothing itself (the core's plugins are added in `Application::bootstrap()`/`bootstrapCli()`) and merges `config/plugins_local.php` when it exists. That file is git-ignored here: it is where an installation adds its own plugins (`return ['MyPlugin' => []];`). Bake is loaded only when `Bake\BakePlugin` exists (no deprecation in a `--no-dev` install).
- **API routes** (`Application::ehRotaApi()`: the path is `/api` or starts with `/api/`) authenticate by token, not by the session cookie, so they skip CSRF (`Application::pulaVerificacaoCsrf()`, the single `skipCheckCallback()` rule) and are never redirected to the login page (`unauthenticatedRedirect` is null for them). The core has no API yet; the rule is for APIs registered by plugins.
- **Errors on API routes** are answered as JSON (`{"mensagem": ...}`, `templates/Error/error_api.php`, no layout) — also for an `/api/...` path with no route (`ErrorController::beforeRender()`).
- A plugin sets these `Kaw.*` keys in its `bootstrap()` **only when they aren't defined yet** (`Configure::check()`), so the installation's `config/app.php` — and the core's tests (`ConfiguracaoKawIsoladaTrait`, which writes the core defaults explicitly because every test request re-runs the plugins' bootstrap) — can override them.
- **Menu items** from a plugin's `bootstrap()`: `Configure::write('Kaw.Menu.configuracoes', [...])` (root-only "Configurações" section) and `Kaw.Menu.outros` ("Outros" section, shown only when it has items). Item: `['url' => array|string, 'icone' => 'bi-...', 'rotulo' => 'Texto', 'dominio' => 'default', 'somenteRoot' => false]`; the label is translated with `__d($dominio, $rotulo)` (`element/Diversos/btnOpcoes.php`).
- **Login page elements**: `Kaw.Login.elementos` lists elements rendered below the login form (e.g. a help link).
- **"Configurações gerais" off**: `Kaw.Configuracoes.regionalEditavel = false` hides the screen from the menu and makes its actions redirect home — for an installation whose time zone and language are written to `configuracoes` by something else.
- **First-run events** (global `EventManager`, e.g. a plugin's `events()`): `UsersController::EVENTO_CONFIG_INICIAL_INICIO` (`Kaw.ConfigInicial.inicio`, when the first step opens while the system isn't configured) and `EVENTO_CONFIG_INICIAL_SEGUNDO_FATOR` (`Kaw.ConfigInicial.segundoFator`, when the 2FA step opens, with the first user in `$event->getData('user')`). Both can fire more than once (on every visit), so listeners must be idempotent.

### Middleware queue order matters

In `Application::middleware()`: ErrorHandler → Asset → Routing → BodyParser → Authentication → CSRF → HttpsEnforcer → SecurityHeadersKaw → **SessionsKawMiddleware** (must run after Authentication). `SessionsKawMiddleware` reads the current PHP session's row from the `sessions` table (populated by CakePHP's `DatabaseSession` handler) and stamps `user_id`/`user_agent` onto it once authenticated, or purges sessions older than a day when unauthenticated. In integration tests, that row doesn't exist unless you insert it yourself (see `AuthenticatedTestTrait`).

### Everything sensitive is encrypted at rest with libsodium, not hashed

`App\Criptografia\Criptografia` wraps `sodium_crypto_secretbox` using `env('KEY_CRIPTOGRAFIC')` as the key; each call generates a fresh nonce, so **encrypting the same plaintext twice produces different ciphertext** — this matters for tests and for auditing/diffing changed fields. It's used (via entity mutators/`*Descrip()` accessors) for `Entrada::titulo/username/password/link/anotacoes`, `Categoria::nome`, and `User::tfa_secret`. `User::password` is the only field hashed instead (bcrypt via `DefaultPasswordHasher`).

### Custom validation: `ValidatorKaw`

`App\Model\Custom\ValidatorKaw extends Validator` adds `checkXSS()`, used throughout `validationDefault()` on every Table. It runs `voku/anti-xss` against the field and, if it finds anything, logs an `xss_detectado` security event (`App\Log\Seguranca`, level `alert`, with table, field and action in the context — never the malicious content).

### Logs (`App\Log`) and security events

Logging: one `FileLog` per level (`debug`, `notice`, `info`, `warning`, `error`, `critical`, `alert`, `emergency`) plus the `seguranca` engine, all formatted as JSON by `App\Log\JsonFormatter` (`timestamp`, `nivel`, `mensagem`, `contexto`, `app`, `ambiente` and a `requisicao` block with request id, method, route — never the raw URL —, IP and user id, from `ContextoRequisicao`). `App\Log\ErrorLogger` (`Error.logger`) logs exceptions with structured fields and HTTP 4xx as `warning`. These three are the core's own implementation (the core depends only on public packages). Configure in `config/app.example.php` (`config/app.php` is not versioned).

- **Security events** go through `App\Log\Seguranca::registrar($nivel, Seguranca::<EVENTO>, 'fixed description', [...data])`: scope `seguranca` → `logs/seguranca.log`, message `[evento] description`, the event name also in the context (`evento`), variable data (typed e-mail, field) in the context, never concatenated in the message. Events: `login_credencial_invalida` and `login_2fa_invalido` (`notice`), `acesso_somente_root` (`warning`), `xss_detectado` (`alert`).
- **CakePHP 5 scope routing:** an engine with `'scopes' => null` only receives logs **without** a scope (`[]` receives all). So the per-level engines don't duplicate the `seguranca` lines, and the `seguranca` engine has `'levels' => []` so no level is dropped.
- This replaced the old event catalog (`GerenciadorEventos`/`Evento`/`EventosComplexos`, the `DatabaseLog` engine, the Logs screen and the home's "Atividades suspeitas"), removed on 2026-11-09. In this edition it never showed data anyway: its `atividades` scope wrote to a file, not to the `logs` table. The `logs` table is still in the database, unused, on purpose: a future events screen for users may reuse it.
- Tests: `tests/TestCase/Log/CapturaLogSegurancaTrait.php` captures the `seguranca` scope in memory with the same formatter; `logSeguranca('<evento>')` returns the decoded line.

### Business audit trail (`App\Auditoria`, `auditorias` table)

Distinct from the technical/security logs above: an immutable, insert-only record of who did what and when on `Entradas`, `Categorias` and `Users`. Each audited Table does `use AuditavelTrait;` + `$this->adicionarAuditoria('<contexto>', mascararAlteracoes: true)` (contexts are lowercase singular: `entrada`, `categoria`, `user`). `AuditoriaBehavior` computes the per-field diff on `afterSave`/`afterDelete`; `AuditoriasTable::registrar()` (`RegistraAuditoriaTrait`) persists it with `['atomic' => false]` and turns a failure into `Log::error` — an audit failure must never roll back the business save. With `mascararAlteracoes: true`, `antes`/`depois` are blanked to `''`: only the changed field **names** are kept (this app stores secrets and the trail is immutable). User and IP come from `Router::getRequest()` (`identity` attribute) — `usuario_id` is `null` when there is no login (first-run `configInicial()`).

- Internal saves that are not a user action must pass `['auditoria_acao' => false]` (e.g. `CategoriasTable::reordenar()`); a string there records a custom business action instead of `criacao`/`edicao`.
- `Entrada::isModified()`/`Categoria::isModified()` decrypt-and-compare before the default dirty check: encryption uses a random nonce, so the edit form (which resends every field) would otherwise flag every encrypted field as changed.
- `auditorias.usuario_id` has **no foreign key on purpose** (migration `20261109100000`): with the old `RESTRICT` FK, deleting a user who appears in the trail failed with a DB error. The `existsIn(['usuario_id'], 'Users')` rule still validates at write time; `Auditoria::descricaoUsuario()` shows "Usuário excluído (#id)" afterwards.
- UI: `AuditoriasController` (`index`/`view`), **open to every logged-in user** since 2026-11-09, linked from the main section of `btnOpcoes.php`; the origin IP in `view` is shown only to root (personal data). The home ("Minha Segurança") lists the 10 latest records for everyone, with links.
- Tests: Table-level tests simulate the identity with `Router::setRequest((new ServerRequest())->withAttribute('identity', new Identity(['id' => 1])))` (see `tests/TestCase/Auditoria/AuditoriaBehaviorTest.php`). Any test class that saves an audited Table should list `'app.Auditorias'` in `$fixtures` so the table is truncated between tests.

### CSRF cookie renewal (`CsrfProtectionKawMiddleware`)

Extends CakePHP's `CsrfProtectionMiddleware`: on **GET**, a CSRF cookie whose HMAC doesn't match the current `SECURITY_SALT` (the salt was rotated, or the app was reinstalled on the same domain) is dropped so a fresh one is issued — otherwise every POST fails with "Missing or invalid CSRF cookie." until the cookie expires. POSTs with an invalid cookie are still rejected. Integration tests skip CSRF entirely (`isRunningUnitTests()`), including cookie issuance, so it is tested directly at the middleware level (`tests/TestCase/Middleware/CsrfProtectionKawMiddlewareTest.php`).

### Root-only access & `beforeFilter()`

Root-only controllers check `$user instanceof User && $user->root` in `beforeFilter()` and, for an authenticated non-root user, flash an error, log an `acesso_somente_root` security event and short-circuit with `$event->setResult($this->redirect([...'Pages', 'home']))` + `$event->stopPropagation()` — **never `return` a response from `beforeFilter()`** (deprecated since CakePHP 5.2, and a child's `parent::beforeFilter($event)` call silently drops the return value, so the action would still run). `UsersController` is root-only for `SOMENTE_ROOT_ACESSA` (index/add/edit/delete). Hiding a menu entry (`btnOpcoes.php` root section) is not access control — every root-only screen needs this server-side check.

### Auth & 2FA

Login (`UsersController::login`) requires email+password *and* a TOTP code (`pragmarx/google2fa`). `User::valida2fa()` skips real verification (accepts any code) whenever `Application::isTheExecutionEnvironment(DESENVOLVIMENTO)` is true — i.e. in local dev and in the test suite itself, since `config/.env` exists and `DEBUG=true` there. To exercise real 2FA verification through the full login flow, integration tests flip `Configure::write('debug', false)` for the request and simulate HTTPS (with debug off, `HttpsEnforcerMiddleware` rejects plain HTTP) — see `UsersControllerTest::postLoginEmModoProducao()`. `User::valida2fa()` also has direct unit tests (`tests/TestCase/Model/Entity/UserTest.php`) for each branch. Login tests send a real current TOTP (`Google2FA::getCurrentOtp()`) even in dev mode, so they don't depend on the bypass. First-run bootstrap (no users yet, or the first user has no 2FA yet: `UsersTable::configInicialSistema()`) is handled by `UsersController::configInicial()` / `configInicialTfa()`, which is a distinct code path from normal login — `configInicialTfa()` is reachable without authentication (see `ACTIONS_SEM_AUTENTICACAO`), so it must `return` its early redirect when the system is already configured, or it will silently regenerate and persist a new 2FA secret for the last user on every hit (this was a real bug, fixed together with the same missing-`return`-before-`redirect()` pattern elsewhere in the same controller — `configInicial()`, `minhaConta()`, `finalizarSessao()`). `UsersController::SOMENTE_ROOT_ACESSA` gates index/add/edit/delete to `root` users only, redirecting non-root users to `Pages::home` and logging an `acesso_somente_root` security event.

The login answers every failure (wrong password, wrong or malformed 2FA code) with the same "Credenciais de acesso incorretas" and no detail: showing the 2FA validation errors only after a correct password would tell an attacker that the password is right. The 2FA setup (`geraQrCode2fa`, JSON, and `configInicialTfa`) also shows the secret grouped in blocks of 4 and the account name, for whoever can't scan the QR code (`element/Users/dadosManuais2fa.php`).

### Flash messages

Controllers pass the message untranslated and the `templates/element/flash/*` elements translate and escape it (`__(h($message))`), so a flash message can't carry HTML. An empty message gets the element's default (`success` → "Salvo com sucesso", `error` → "Ocorreu um erro"); `['params' => ['mensagens' => $entidade->getErrors()]]` (or `'entidades' => [...]`) lists the validation errors, flattened by `AppView::extraiMensagens()` (`App\View\Tools\ManipuladorMensagemValidatorTrait`).

### Time zone and language (`configuracoes`, "Configurações gerais")

The root user picks the installation's time zone and language at `/configuracoes/geral` (`ConfiguracoesController::geral()`, root-only). They are stored in the key/value `configuracoes` table (`Configuracao::REGIONAL_FUSO_HORARIO`/`REGIONAL_IDIOMA`, saved with `ConfiguracoesTable::salvarValorConfig()`) and applied on every request by `config/bootstrap.php`: `ConfiguracoesTable::preferenciasRegionais()` → `App\I18n\Regional::aplicar()` (PHP default time zone, `App.defaultTimezone`, `App.defaultLocale`, `intl.default_locale`, `I18n::setLocale()`). Without the table (fresh install, before the migrations) or without a stored value, `config/app.php`'s defaults apply. `Regional::IDIOMAS` lists the languages with a translation (`pt_BR` is the source text, `es` comes from `resources/locales/es/default.po` — only core phrases; when adding a `__()` phrase, add its Spanish there too, including text passed to elements that translate dynamically, like `btnNovo`'s `texto`, breadcrumb parts, tooltips and flash messages); `Regional::idiomaDoNavegador()` picks one from `Accept-Language` where nothing is configured yet (first-run `configInicial`). Changing the time zone resets the ORM's cached date/time types (`TypeFactory::setMap()`), otherwise datetime columns read later in the request would be shown in the previous zone.

### Front-end assets (no minification step)

Bootstrap and Bootstrap Icons come from Composer (`twbs/bootstrap`, `twbs/bootstrap-icons`) and are copied into `webroot/` by `ArthuSantiago\BootstrapForCakePHP\BootstrapAssets::setupAssets` (`post-install-cmd`/`post-update-cmd`): `css/bootstrap.min.css`, `css/bootstrap-icons.min.css`, `js/bootstrap.min.js` and `fonts/bootstrap-icons/`. The copied files are versioned, so commit them after a `composer update` that bumps either package. The app's own assets (`css/geral.css`, `js/ferramentas.js`...) are served as they are; there is no minification tool. `js/popper.min.js` (needed by `bootstrap.min.js`, which is not the bundle) and `js/easytimer.min.js` are third-party files committed as is. Layouts load them by name (`$this->Html->css('bootstrap.min')`).

### Testing conventions

- Postgres sequences: any fixture with an explicit integer `id` must extend `tests/Fixture/AppFixture.php` (not `TestFixture` directly), which resyncs the sequence after insert — otherwise the next `save()` in that test collides on a duplicate key.
- Controller integration tests `use CsrfTestTrait;` (a `#[Before]` hook that calls `enableCsrfToken(Application::nomeCookieCsrf())`). The cookie name depends on the environment (`Secure-csrfToken` in development, `__Secure-csrfToken` otherwise), so always use `Application::nomeCookieCsrf()`, never a literal; a test that flips `debug` must enable the token again afterwards. To send a request without the token, set `$this->_csrfToken = false;` (see `PagesControllerTest::testCsrfAppliedError()`, which expects 403).
- `tests/TestCase/Controller/AuthenticatedTestTrait.php`'s `loginAsUser()`/`loginAsNonRootUser()` fake an authenticated session without going through a real login POST; it also inserts a matching `sessions` row (id `'cli'`, set in `tests/bootstrap.php`) so `SessionsKawMiddleware` doesn't choke on a missing row. The fake identity also carries a `tfa_secret` so actions that call `descripSecret2FA()` on the session identity directly (e.g. `UsersController::geraQrCode2fa()` for your own account) don't crash on a null value — a real session identity is loaded whole from the DB, this one isn't.
- Encrypted fields in fixtures must be pre-encrypted with `Criptografia::criptografar()` (see `EntradasFixture`, `CategoriasFixture`) — raw plaintext in a fixture will fail to decrypt when a test calls `*Descrip()`.
- To assert on session contents after an integration request, read `$_SESSION` directly (as Cake's `assertSession()` does) — **not** `$this->_requestSession->read()`: `Server::run()` closes the session at the end of the request, and in CLI a later `Session::read()` restarts it empty. To carry a real session into the next request (login → page → logout), reset `$this->_session = []` before `$this->session($_SESSION)`, since `session()` merges with what was set before (see `UsersControllerTest::reutilizarSessaoDoUltimoRequest()`).
- `User::_accessible` guards `root`, `tfa_ativo` and `tfa_secret` — `$usersTable->newEntity([...])` silently drops them. Set them with direct property assignment on the returned entity instead (`$user->root = true;`) before saving, the same way the controller code itself does.
