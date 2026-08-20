# Zoosper CMS — Master Roadmap

**Last updated:** 2026-08-16 (Sydney)

## Current continuity status

- Latest tag: `v0.3.0-alpha.2`.
- Current development line: `v0.3.0-alpha.3-dev`.
- Completed API arc: 10AK, 10AL, 10AM-A, 10AM-B, 10AM-C, 10AN-A, 10AN-B/C and 10AO.
- Next bulk: 10AP-A Media reads and derivatives, 10AP-B canonical PAT upload, and 10AP-C archive/restore plus reference-safe permanent deletion where the shared reference contract permits it.

---

> **Single source of truth for high-level feature status.**
> High-level only — one line per capability, not per micro-phase. Detailed phase
> notes live under `docs/`. Update this file once a day at wrap-up: tick completed
> items, add newly planned ones, move things between sections.
>
> **CONTINUITY NOTE:** this file is the recovery mechanism if a chat session is
> ever lost. It has been verified to work: a fresh conversation can retrieve
> this file directly from OneDrive/SharePoint and reconstruct full project
> state without any chat history. Keep it genuinely current, not just at
> wrap-up — the more accurate this file is, the less any single conversation
> thread actually matters.

**Framework baseline:** PHP 8.5 · Pest/PHPUnit · Psalm · Latte · **Marko packages
(real, adopted usage — see §14) via zoosper/errors and zoosper/core**

Legend: `[x]` done & deployed · `[~]` in progress / partial · `[ ]` planned
`[R]` reported by external reviewer (Fable pass #1, 2026-07-29), verification status noted inline
`[R2]` reported by external reviewer (Fable pass #2, 2026-07-30), verification status noted inline
`[FIXED]` confirmed fixed and deployed this session

---

## 0. TOP PRIORITY — next phase

**Both items from the 2026-07-29 top-priority list are now confirmed and
fixed:**

- **[FIXED] Privilege escalation via OR-permission routes.** Confirmed real:
  `/admin/users/edit` is gated by `['role.manage', 'user.manage']` (either
  sufficient), and `UserAdminController::update()`/`create()` previously
  wrote submitted `role_ids` unconditionally regardless of which permission
  the actor actually held. Fixed: role assignment now requires
  `role.manage` specifically. `update()` preserves existing roles if the
  actor lacks it; `create()` fails closed with a clear error. Verified with
  a test that reproduces the actual exploit path and confirms it's blocked,
  plus confirms a legitimate `role.manage` admin's workflow is unaffected.
  `RoleAdminController`'s single-permission gate (no OR) was deliberately
  left alone — architecturally different, not the same bug — flagged as a
  separate product decision if you ever want it tightened.
- **[FIXED] Rate-limit store race condition.** Confirmed real:
  `DatabaseRateLimitStore::recordAttempt()` did SELECT → branch →
  INSERT/UPDATE with no atomic upsert; two concurrent requests for the same
  identity+window could both miss the row and the second would throw an
  uncaught `PDOException` → 500. Fixed with a single atomic upsert
  statement per driver (MySQL `ON DUPLICATE KEY UPDATE`, SQLite
  `ON CONFLICT DO UPDATE`), the same pattern already used correctly
  elsewhere (`EntityExtensionValueRepository::upsert()`).

**Fixed, from a live production incident (2026-07-30):**

- **[FIXED] 2FA encryption key rotation caused a real admin lockout.**
  Confirmed via live `exception.log` + nginx access log: an admin who
  enrolled in 2FA before `TWO_FACTOR_ENCRYPTION_KEY` was set (or before it
  was later changed) became permanently unable to log in —
  `SecretProtector::reveal()` only ever tried a single key. Fixed with real
  key-rotation support (current key for encrypting; current + a list of
  previous keys tried for decrypting — the same pattern Laravel uses via
  `APP_PREVIOUS_KEYS`), plus opportunistic re-encryption on successful
  login so each admin's rotation window closes itself automatically.
  Recovered live via `TWO_FACTOR_PREVIOUS_ENCRYPTION_KEYS`. `APP_KEY` was
  separately confirmed to have zero other consumers in the codebase and is
  now safe to rotate independently at any time.

**Fixed, from a second Fable review pass (2026-07-30):**

- **[FIXED] `bin/zoosper deploy` migrated against a stale cached module
  list.** Confirmed real: once module-list compilation was added, a release
  that adds a new module could have its migrations silently skipped if an
  older compiled cache still existed on disk — regardless of `deploy`'s
  step order. Fixed at the root (not just by reordering steps, which would
  only protect the `deploy` command specifically): `Migrator` and
  `SchemaLoader` now both call `ModuleRegistry::discoverModulesLive()`
  instead of `enabledModules()`, so migrations always see live, current
  truth. Verified with a test that directly reproduces the exact scenario
  (compile a cache, add a module afterward, confirm its migration still
  runs).

**Still open, from the second Fable review pass:**

- **[FIXED, Phase 9GE] CLI recovery commands are database-independent.** `help`, `list`, `compile`, `cache:clear` and manifest inspection execute without resolving PDO. The shared `PdoConnectionProvider` remains lazy and module command services receive PDO through a lazy container factory.
- **[FIXED, Phase 9GE] CLI and HTTP share layered configuration.** Both boot paths use `ApplicationConfigLoader` with module defaults below root overrides; the remaining console discovery regression fixture was migrated off the obsolete root-only loader.
- **[RESOLVED] Redundant test/session and one-shot cleanup scripts retired** —
  `composer test` remains the canonical Pest entry point. The unused
  `bin/pest.sh`, interactive `collect-and-run.sh`, and completed one-shot
  cleanup scripts were removed in Phase 2C.

---

## 14. Marko Framework Adoption Strategy

The project's original architecture intent (see `README.md`: "...inspired
by ... Marko PHP module conventions") was to build as much of Zoosper as
possible on top of real Marko packages rather than reinventing them. That
intent was not consistently followed prior to 2026-07-30 — `marko/core`,
`marko/errors`, `marko/errors-simple` were installed in `vendor/` but
completely unused. The discipline going forward: **before writing any new
subsystem from scratch, check Marko's real package catalog first** (by
reading actual installed source / real docs — not guessing from package
names), and this is now an explicit project rule in `AGENTS.md`.

### Adopted (real, verified integration)

- **`marko/core` (`MarkoException`)** — `ZoosperException` now formally
  `extends MarkoException` (additive: `ZoosperException` was already a
  strict superset — `docsUrl`/`details` have no Marko equivalent).
- **`marko/errors` (`ErrorReport`, `Severity`)** — real error-reporting
  pipeline; `ZoosperException` is automatically recognised by
  `ErrorReport::fromThrowable()`'s own `instanceof MarkoException` check,
  with zero glue code.
- **`marko/errors-simple` (`TextFormatter`, `BasicHtmlFormatter`,
  `CodeSnippetExtractor`, `Environment`)** — real CLI/web exception display,
  wired via `Zoosper\Errors\ExceptionDisplayer`. Deliberately NOT a
  wholesale replacement of `ErrorHandler` with Marko's own
  `SimpleErrorHandler` — that class has no file-based logging at all;
  composing (log via existing `LocalLogger`, then display via Marko's
  formatters) preserves existing log output while adding real display.
- **`marko/cache` + `marko/cache-file` + `marko/cache-redis`** — a real,
  configurable (file or Redis, one config flag) cache backend, via
  `Zoosper\Core\Cache\CacheDriverFactory`. Both drivers installed and
  tested (the Redis test attempts a real connection and honestly skips —
  never falsely passes/fails — if Redis isn't reachable).
- **`marko/config` (`ConfigRepositoryInterface`)** — required transitively
  by `marko/cache`'s `CacheConfig`/`marko/encryption`'s `EncryptionConfig`.
  Satisfied via a new `Zoosper\Core\Config\MarkoConfigRepositoryAdapter`,
  wrapping Zoosper's own `ConfigRepository`. Deliberately kept *inside*
  `zoosper-core` rather than a new `zoosper/config` package: a separate
  package wrapping `zoosper-core`'s own `ConfigRepository` would create a
  circular dependency (`zoosper-core` needing the new package; the new
  package needing `zoosper-core`). See "Deferred" below for the real,
  larger extraction this points toward.
- **`marko/encryption` (`EncryptionConfig`)** — backs
  `Marko\Cache\Redis\Signer\CacheValueSigner`'s HMAC tamper-detection for
  Redis-cached values. Uses a dedicated `CACHE_ENCRYPTION_KEY` — never a
  reuse of `APP_KEY`/`TWO_FACTOR_ENCRYPTION_KEY`, matching the
  single-purpose-key discipline already applied to 2FA.

### New package extracted: `zoosper/errors`

First module extracted out of `zoosper-core` into its own standalone
package (`packages/zoosper-errors`), following the same path-repository
pattern already proven by `packages/zoosper-media`. Owns
`ZoosperException`, `SensitiveValueRedactor`, `ConsoleExceptionFormatter`,
`ExceptionDisplayer` (owns every direct `Marko\*` import — `zoosper-core`
itself has **zero** direct Marko dependency in its own `composer.json` for
error handling, only `zoosper/errors`, which transitively provides
`marko/core`+`marko/errors`+`marko/errors-simple`). Has its own
`.gitattributes` marking `tests/`/`phpunit.xml.dist` as `export-ignore`,
matching Marko's own real package convention (confirmed by reading
Marko's actual GitHub repos directly).

**`.gitattributes` timing, settled**: only added at the moment a module is
*actually extracted* into `packages/` as a real, separately-exportable
package. `app/zoosper-*` modules are path-repository entries within the
monorepo, not separately-exported packages — `export-ignore` would be
inert for them today (there is no "export" event happening). Add it when
each module gets extracted, not before.

**Next extraction candidate: logger.** `LogManager`/`LocalLogger` (in
`zoosper-core`) map almost one-to-one onto `marko/log` + `marko/log-file`
(confirmed real via a targeted search: a PSR-3-compatible `LoggerInterface`,
`LogLevel` enum, `LogRecord`, `LineFormatter`, file rotation) — genuinely
the next-cleanest extraction candidate after errors, per the project
owner's explicit request. **Not yet started** — needs the same
read-real-source-first discipline applied before designing the extraction
(check `marko/log`'s real constructor/interface shape, confirm no
circular-dependency trap the way config nearly had one).

### Evaluated and explicitly deferred: `marko/database`, `marko/database-mysql`

Real, attribute-driven entity mapper (`#[Table]`/`#[Column]` classes,
auto-generated migrations, `QueryBuilderInterface`, `Repository` +
`EntityCollection`) — genuinely comparable in ambition to this project's
own `Schema/`+`Database/` namespaces. **Deliberately not adopted**: this
would mean rewriting the entire persistence layer (every module's
`config/db_schema.php`, `SchemaSqlBuilder`/`SchemaMigrator`/
`SchemaValidator`/`SchemaLoader`/`Migrator`/`ConnectionFactory`, and every
repository across every module) — touching literally everything hardened
this session (`EMULATE_PREPARES`, collation, `tableExists()`'s MySQL-
protocol fix, the compile step, the stale-cache fix). This is a dedicated,
separate planning conversation, not a quick extension. **Also flagged**: a
possible version mismatch between `marko/database-mysql` (requires
`marko/database: 0.6.1`) and `marko/database` itself (listed at `0.8.4`) —
needs verifying directly before trusting this stack for anything real; no
SQLite driver was found in the docs reviewed, which would be a real
concern given this project's dual-driver (SQLite test / MySQL prod)
testing approach.

**Root `composer.json` cleaned up accordingly (2026-07-31)**:
`marko/database`, `marko/database-mysql`, and `marko/framework` (a
metapackage that pulled in the above plus `marko/cli`/`marko/hashing`/
`marko/routing`/`marko/validation`, none of which anything actually used)
were all removed from the **root** `composer.json` — confirmed via a real
`composer update` that all 7 transitively-unused packages were cleanly
removed with zero breakage. Every Marko package Zoosper actually uses is
now declared as a **direct dependency of the specific module that uses
it** (`zoosper/errors` → `marko/core`+`marko/errors`+`marko/errors-simple`;
`zoosper/core` → `marko/cache`+`marko/cache-file`+`marko/cache-redis`+
`marko/config`+`marko/encryption`) — the root project itself now has zero
direct Marko dependencies, matching the same "depend on what you actually
use, at the module that actually uses it" discipline already applied
throughout this session.

### Adopted for read-replica support: `marko/database-readwrite` (queued, not yet built)

A small, genuinely additive decorator: routes writes to one primary
connection and reads to one or more replicas, wrapping *any* existing
`marko/database`-compatible connection. **Important, honestly-flagged
correction to earlier analysis**: this package requires a real
`Marko\Database\ConnectionInterface`/`TransactionInterface`-compatible
connection — i.e. it is NOT a generic wrapper around any PDO connection;
it depends on the `marko/database` connection abstraction specifically.
This means genuine read-replica support via this package is **not** fully
decoupled from the `marko/database` adoption question above the way
originally believed — needs the real `ConnectionInterface` source read
directly before designing anything further here. Queued, not started.

### Not yet researched — queued for the next planning pass

- **`marko/cli`** — relevant to the "every CLI command requires a live
  database" bug (§0 [R2]) and the broader `bin/zoosper` structure.

### Noted overlap, not being pursued now (bigger architectural decision)

- **`marko/authentication`** (`AuthManager`, `SessionGuard`, `TokenGuard`,
  `AuthenticatableInterface`) and **`marko/authorization`**
  (`GateInterface`, Policies, `#[Can]` attribute) and **`marko/admin-auth`**
  (`PermissionRegistry` with wildcard matching, `#[RequiresPermission]`) —
  conceptually parallel to this project's own `SessionGuard`, ACL tree, and
  manual permission-check `if` statements scattered through controllers.
  **Confirmed: Marko has no 2FA/TOTP/MFA package at all** — searched
  directly, including all four linked package docs plus a dedicated search
  for `marko/two-factor`/`totp`/`mfa`/`otp` — so `zoosper-two-factor` is
  genuinely original and correctly should NOT be replaced. The
  authentication/authorization overlap is a real, bigger decision
  (potentially shifting the whole permission-checking approach to
  attribute-based declarations) — worth a dedicated evaluation later, not
  rushed.

---

## 15. High-Traffic & Read-Replica Readiness

Planning assumption, stated explicitly by the project owner: this CMS may
need to support a high-traffic website and may require a database read
replica.

- **[FIXED, foundation] HTTP cache subsystem is no longer inert.**
  `CacheDriverFactory` (via `marko/cache` + file/Redis drivers) provides a
  real, working, configurable cache backend. On top of it,
  `Zoosper\Core\Routing\CachingFallbackHandler` provides a real, opt-in
  frontend page cache (disabled by default via `config/page_cache.php`) —
  decorates any `FallbackHandlerInterface`, fails open on every unsafe
  condition (non-GET, no `SiteContext`, non-200 response, cache backend
  errors), never breaks page rendering because of a caching problem. A
  confirmed, real incompatibility was found and fixed while building this:
  `CacheKeyBuilder`'s own key format (`:`-separated, `/`-permitting) is
  explicitly rejected by Marko's own cache-key validation — fixed by
  hashing the correctly-computed structured key into a guaranteed-safe
  format, verified directly against the real, installed `FileCacheDriver`
  (not just a fake). **Two honestly-stated, still-open limitations**: does
  not vary the cache key by query string (no generic raw-query-string
  accessor exists on `Request` yet — deliberately not reaching around that
  via `$_SERVER`, matching the `Request::form()` immutability discipline);
  assumes one theme per site (the decorator only has the `Request`, not
  the resolved `Site` model).
- **Read replica support**: see §14 — `marko/database-readwrite` requires
  a `marko/database`-compatible connection, so this is not as fully
  decoupled from the (deferred) `marko/database` adoption question as
  first believed. Needs its real `ConnectionInterface` contract read
  before any further design.
- **Extend the compile step beyond just the module list** (§1, §13.2) —
  currently only `ModuleRegistry`'s module list is cached; ~14 other
  per-request loops (routes, services, controllers, events, ACL,
  translations, grid columns, etc.) still re-scan every request. This
  matters far more at high traffic than at low.
- **Instance-level memoization risk under long-lived workers** (§4) —
  `SessionGuard::$cachedUser` and similar per-instance caches are safe
  under classic PHP-FPM (fresh process per request) but become a genuine
  cross-request session-confusion risk if high traffic ever motivates a
  move to Swoole/FrankenPHP-style long-lived workers. Needs an explicit
  "rebuild request-scoped services per request" story before any such move
  — not just the existing manual `clearCache()` escape hatch on one class.
- **Rate-limit enforcement** (currently report-only only, per the
  project's own ADR) — more traffic means more exposed attack surface for
  longer before report-only data is even reviewed. Still correctly gated
  on collecting real report-only data first, per the ADR — but worth
  revisiting the timeline given the traffic assumption.
- **Every CLI command requiring a live DB** (§0 R2) and **CLI/HTTP config
  divergence** (§0 R2) become more painful operationally at higher
  deployment frequency/scale (cron jobs, scaling scripts, health checks).

---

## 1. Core Platform & Architecture

- [x] Modular package layout (`app/zoosper-*`, plus `packages/` for
  extracted standalone modules) with per-module `composer.json` + PSR-4
- [x] Service container + module-owned `config/services.php` providers
- [x] Module route loader (admin + API) & controller providers
- [x] Config aggregation from module `config/*` (HTTP path only — see §0 R2
  for the CLI divergence)
- [x] Declarative schema engine (validator, migrator, snapshot audit)
- [x] Driver-aware schema detection (MySQL + SQLite)
- [x] Entity save lifecycle + listener discovery
- [x] Entity extension (EAV) data persistence
- [x] Event/observer bus (module-discovered listeners)
- [x] Method plugin / interceptor system (report-only seam)
- [x] Core ⇄ feature decoupling (+ behavioural namespace-ban guard test,
  independently praised by reviewer passes as "legitimately well-built")
- [x] Site-lookup boundary: core-owned `SiteLookupInterface` + `NullSiteLookup`
- [x] Frontend fallback handler wiring (fatal fixed) + boot-and-serve test
- [x] All 12 first-party modules promoted to real Composer packages (Phase 1.40)
- [x] Module-owned migrations (Phase 1.40c)
- [x] Console/kernel decoupling — `admin:create`/`site:create`/`page:create`
  discovered per-module via `ModuleConsoleCommandLoader`
- [x] **Module manifest compile step** (`bin/zoosper compile`/`cache:clear`/
  `deploy`) — caches the module *list* to `var/cache/modules.php`, safe
  fail-back to live scan if missing/corrupt.
- [x] **[FIXED] Migrations always use live module discovery** — see §0,
  the stale-cache deploy bug.
- [x] **[FIXED] Real, configurable page cache foundation** — see §15.
- [x] Admin/module dependency decoupling: two-factor and media are complete; shared presentation contracts moved to Core in Phase 9FR, and Page and Settings no longer require `zoosper/admin`.
- [ ] **[R] Layered module discovery collision diagnostics.** `ModuleRegistry` scans four runtime patterns: `app/*/module.php`, `modules/*/module.php`, `modules/*/*/module.php`, and Composer packages under `vendor/*/*`. Same-layer duplicate identities throw `DuplicateModuleException`; cross-layer identities resolve silently by app > modules > vendor priority. Add an explicit diagnostic for cross-layer overrides before further package extractions.
- [ ] **[R] No FK support in the declarative schema engine, no
  down-migrations.** Zero referential integrity on any table built through
  it. Low blast-radius today only because no admin screen supports
  deleting anything yet (see §2). Not yet fixed.
- [ ] Container autowiring
- [ ] Module lifecycle (install/enable/disable/uninstall)
- [ ] Composer packaging + 0.x tag + CHANGELOG + stability contract — every
  internal module dependency still uses unconstrained `*@dev`

## 2. Sites, Pages & Content

- [x] Multi-site + site domains (store-view model) + admin CRUD
- [x] Pages CRUD (admin) + revisions
- [x] SEO metadata fields
- [x] Editor.js content model + JSON save pipeline
- [x] Block JSON → HTML rendering + HTML sanitization (HTMLPurifier)
- [x] Frontend page rendering via themes
- [~] `content_json` frontend rendering via `PageRenderer` (planned deepening)
- [ ] Router path parameters
- [ ] Consolidate `pages` table into declarative schema
- [ ] **[R] No delete/archive on any admin CRUD screen** — flagged as "a
  basic missing feature," and the reason the missing-FK gap hasn't caused
  visible damage yet. Not yet built.

## 3. Themes & Templating

- [x] Latte + PHP template engine adapters
- [x] Theme repository + per-site theme selection + theme admin
- [x] Module/theme template overrides (path-safe) + layout update system
- [~] RoleAdmin → Latte cutover (users on Latte; roles still PHP views)
- [ ] Adopter theme override story documented end-to-end
- [~] **CSP enforcement readiness.** Phase 9GA removed the verified inline Reset 2FA handler and moved confirmation behaviour into a registered Auth asset. CSP remains report-only; reporting endpoint configuration and broader browser verification remain before enforcement.

## 4. Admin & Auth
- [~] **Unified admin grid workspace.** The Pages grid now supports configurable
  columns, filters, page size, CSV export, bookmark persistence, draggable
  ordering with locked ID/Actions anchors, and immediate live table reflection.
  `zoosper-admin-grid` now owns the canonical generic column runtime; the
  application bridge is parity-guarded compatibility wiring. Content-derived
  asset versions and JavaScript syntax checks are enforced. Server-rendered header keys are now explicit and positional inference is
  removed. Application compatibility assets are retired. Remaining: add DOM coverage
  before rolling the workspace out to every admin grid.

- [x] Admin authentication + session guard
- [x] Roles, permissions, ACL tree + admin users CRUD
- [x] CSRF + auth middleware pipeline (OR-permission semantics)
- [x] Audit log + login history
- [x] Admin navigation / dynamic menu
- [~] Admin form section + processor registries — only actually used by
  the Page form; every other admin form still uses the older,
  non-extensible `AdminFormDefinition`/`AdminFormField` pair
- [x] i18n / translations / admin locale preference
- [x] 2FA (TOTP) enrolment, reset, recovery-code generation
- [x] 2FA enforced at login (Phase 1.107), with recovery-code redemption
- [x] Login history recording bug fixed (Phase 1.113)
- [x] **[FIXED] 2FA encryption key rotation support** — see §0, the real
  production lockout incident.
- [x] **[RESOLVED] Parallel 2FA stack concern verified as already retired.** Regression coverage confirms the obsolete `TwoFactorSecretProtector`, `AdminTwoFactorRepository`, and British-spelling enrolment service are absent; the live Crypto/Totp/Recovery/Enrollment stack is the only implementation.
- [x] **[FIXED] `bin/zoosper key:generate` / insecure default key** —
  `config/two_factor.php`'s literal-default fallback removed entirely;
  `SecretProtector`'s service factory now fails loudly if no real key is
  configured (enforced at point of use, not in the eagerly-loaded config
  file, to avoid breaking unrelated boot paths).
- [ ] Memoize `SessionGuard::user()` per request — see §15 for why this
  matters more now (long-lived-worker risk)
- [~] Admin god-module split — Page/User/Role admin controllers relocated;
  `ThemeAdminController` not yet moved.
- [x] Batch-load permissions in `AdminUserRepository` (fix N+1) — Phase 1.109
- [ ] Pagination + retention for audit log & login history
- [ ] **[R] Two competing Grid extensibility systems** — the newer
  `GridDefinition`/`GridCriteria`/`GridColumnRegistry` genuinely supports
  third-party column contribution for Audit Log and Login History only;
  Pages/Sites/Domains/Roles/Media still use older, non-extensible pairs.
- [x] **[FIXED] Grid "sortable" columns silently ignored.**
  `AuditLogRepository`/`LoginHistoryRepository` hardcoded `ORDER BY id
  <direction>`, never consulting `$criteria->sortBy`. Fixed with an
  explicit, safe allow-list mapping known sort keys to column expressions
  (not raw SQL interpolation) — zero behavior change today (only
  `created_at` is currently declared sortable), but now genuinely
  extensible for future sortable columns.

## 5. Security

- [x] Baseline security headers, secure session cookie defaults, CSP
  (report-only) + HSTS, constant-time authentication
- [x] Rate-limiting subsystem wired onto `/admin/login`, report-only mode
- [x] **[FIXED] `PDO::ATTR_EMULATE_PREPARES` never explicitly disabled** —
  now `false` for MySQL/MariaDB; real server-side prepared statements.
  (This surfaced a real, separate bug: `Migrator::tableExists()`'s
  `SHOW TABLES LIKE :table` doesn't support real bound parameters in
  MySQL's protocol — fixed by switching to `information_schema.TABLES`.)
- [x] **[FIXED] Rate-limit identity salt defaulted to empty string** — now
  configurable via `RATE_LIMIT_IDENTITY_SALT`, with enforcement at the
  point of actual use (only when rate limiting is explicitly enabled) so
  the default, disabled installation is unaffected.
- [x] **[FIXED] `HTML_SANITIZER_DRIVER=basic` had no production guard** —
  now requires an explicit, separate `HTML_SANITIZER_ALLOW_BASIC_DRIVER=true`
  confirmation; also fixed a confirmed, real `??`/`?:` operator-precedence
  bug in that same config file's `$env` closure.
- [ ] Enable report-only rate-limit mode in production config and begin
  collecting real data — precondition (per the ADR) before enforcement.
  "Enforcement" itself still has no code path built at all (deliberately
  deferred per the ADR) — see §15 for revisiting this given the
  high-traffic assumption.
- [ ] CSP report-only → enforce (after adding report-uri + resolving the
  inline-handler conflict — see §3)
- [ ] Password min-length/complexity + `password_needs_rehash()` upgrade path
- [ ] Prod fail-closed when `SESSION_SECURE` unset
- [ ] CSRF decision for stateful `/api/*` session routes
- [x] Atomic admin writes (transaction-wrap user/role create+sync) — fixed
  in both `RoleRepository` and `AdminUserRepository`
- [x] **[FIXED] `Request::form()` read live `$_POST` directly**, breaking
  its own immutability contract (every other accessor is pure/constructor-
  injected). Now reads from an immutable, constructor-provided property,
  captured once in `fromGlobals()`. No backward-compat shim added (per
  explicit project decision — pre-launch, no external users).
- [ ] Structural email-log body redaction; `entity_extension_values`
  write-time field validation; truncate `user_agent` in audit/login-history
- [x] **[FIXED] `bootstrap/autoload.php` — 4 confirmed bugs**: the dead
  fallback autoloader (only mapped 6 of 12+ namespaces, replaced with a
  clear fail-fast error), the `env()` `??`/`?:` operator-precedence bug,
  3 real `.env` parser bugs (inline comments, quote-stripping, `putenv()`
  consistency), and a missing `function_exists()` guard. **Still open**:
  a reported third, competing env implementation pair (`Core\Bootstrap\
  EnvLoader`, `Core\Env\`) coexisting with the global `env()` — not yet
  investigated, needs those specific files read before consolidating.
- [x] **[FIXED] PDO connected before the error handler registered** —
  `ApplicationFactory::create()` now registers `ErrorHandler` first. **[R2]
  Still open**: `ModuleRegistry` construction and
  `ModuleConfigAggregator::aggregate()` still run before the error handler
  too, and those `require` every module's `module.php`/config files
  (arbitrary code) — a parse error there still surfaces through raw
  `display_errors`. Full fix needs the error handler registered before
  *any* module discovery, not just before the DB connection.
- [x] **[FIXED] `MediaUploadServiceResult::$stored` typed `?object`**
  instead of the concrete `StoredMediaFile` — now properly typed, with an
  explicit runtime guard in `MediaEditorJsUploadController` that throws
  loudly (rather than silently degrading to an empty `publicPath`) if the
  now-impossible null-stored-but-successful state is ever reached.
- [x] LICENSE (MIT) + SECURITY.md added — closes a real repo-hygiene/legal
  ambiguity gap flagged by an external reviewer pass.

## 6. Media

- [x] Media library + admin upload; Editor.js image integration
- [x] Media standalone package split — confirmed complete
- [x] Fixed: `MediaAdminController::upload()` silently swallowed all
  upload failures
- [ ] **[R] Media derivative processing (resize/transform) reportedly
  100% dead in production** — the dispatcher/policy/processor classes are
  built and smoke-tested, but `services.php` never actually passes a
  `derivatives:` argument to `MediaUploadService`. Duplicate MediaUploadService construction is resolved; derivative processing remains open and unwired.
- [ ] **[R] Both media upload controllers reportedly construct their own
  private `MediaUploadService`** instead of the container-configured one
  (with cleanup/derivative dispatcher wired in). Duplicate MediaUploadService construction is resolved; derivative processing remains open and unwired.

## 7. Mail

- [x] SMTP mailer + logged mailer + email log repository/admin viewer
- [x] Mail diagnostics + Mailpit local testing

## 8. API

- [x] API module (Auth, ContentPage, Health, Me)
- [ ] Headless API parity (roles, themes, url-rewrites CRUD)
- [ ] ContentPage API exposes structured Editor.js JSON (not serialized HTML)

## 9. Modular Asset Pipeline
- [~] **Module-owned admin grid assets are live through `/asset`.** Remaining:
  eliminate any runtime dependency on vendor edits, retain the package as the canonical source, retire the compatibility copy
  once package asset routing is live, and extend rendered-URL integration coverage.

- [x] Asset registry / resolver / controller (path-safe, MIME allowlist, ETag)
- [ ] Wire `/asset/{module}/{path}` route + `asset()` helper live
- [ ] Cache asset-registry scans per request
- [~] **Asset resolver adversarial coverage.** Core's `AssetResolver` already has traversal rejection and realpath containment, with direct and URL-encoded traversal tests. Extend coverage for null bytes and symlink edge cases; the containment layer itself is not missing.

## 10. Caching & Performance

- [x] **[FIXED] Real, configurable (file/Redis) cache foundation wired** —
  see §14/§15. `CacheDriverFactory` + `Zoosper\Core\Routing\
  CachingFallbackHandler` (opt-in frontend page cache, disabled by
  default).
- [x] **[FIXED] Unbounded `?page=` in `Pager::fromQuery()`** —
  `page_size` was clamped but `page` was not, allowing an arbitrarily huge
  `OFFSET`. Fixed with a fixed, generous safety ceiling (default 100,000),
  mirroring the existing `page_size` cap.
- [x] **[FIXED] No `COLLATE` pinned in generated `CREATE TABLE`** —
  `SchemaSqlBuilder` now explicitly pins `utf8mb4_unicode_ci` on the MySQL
  branch, preventing silently different collation behavior across
  environments running different MariaDB point releases. Only affects
  newly-created tables going forward, not existing live data.
- [ ] Cache merged translation catalogue per locale
- [ ] Rate-limit report sink rotation/retention (or DB store)
- [ ] Vary page cache key by query string — needs a real
  `Request::queryString()` accessor first (see §15's honestly-stated
  limitation).

## 11. Quality, Tooling & Repo Hygiene
- [ ] Add JavaScript syntax validation for every shipped admin asset.
- [ ] Add DOM behavioural coverage for column drag, live reflection, locked
  anchors, dirty state and bookmark reload.
- [ ] Replace duplicate source-string tests with one behavioural contract.
- [ ] Keep one canonical admin-grid column customisation guide rather than
  phase/hotfix documentation fragments.

- [x] Pest + PHPUnit harness; quality gate runner
- [x] **`.gitattributes` (`export-ignore` for `tests/`/dev tooling) added
  to both extracted packages** (`zoosper-errors`, `zoosper-media`),
  matching Marko's own real package convention (confirmed by reading
  Marko's actual GitHub repos). Deliberately NOT added to `app/zoosper-*`
  modules yet — they are path-repository entries, not separately-exported
  packages, so the mechanism would be inert there today; add at the
  moment each module is actually extracted into `packages/`.
- [ ] **[R] Durable-tool manifest exists purely to stop cleanup automation
  from deleting scripts a Pest test asserts exist** — "inverted," per
  reviewer framing. Worth sitting with, not a quick fix.
- [x] Boot-and-serve feature test
- [ ] **[R2] ~150+ single-purpose tooling scripts still in `tools/`**,
  several existing solely to plan/audit deletion of other scripts in the
  same directory. Not yet pruned further.
- [x] **[R2] AI-session and completed one-shot cleanup scripts removed**
  in Phase 2C.
- [ ] CI workflow (validate, Psalm, Pest+coverage, gate on every PR)
- [ ] Fix composer `gate` script to `@php` (not hardcoded `php8.5`)
- [x] **[R2] Redundant `bin/pest.sh` removed; `composer test` remains the
  canonical test entry point.**
- [ ] **[R] Test-suite signal-to-noise ratio** — a `LegacyVerify*Test`
  family and a 15+ file Page Momentum test cluster are largely
  file-content-assertion "tests," not behavioral ones. Real, good tests do
  exist alongside them — this is about ratio, not total absence.
- [ ] **[R] No public/internal API boundary between feature modules** —
  `CoreDecouplingArchitectureTest` only enforces Core→feature; nothing
  enforces boundaries between feature modules.
- [x] **Comment-verbosity convention decided**: shift from detailed
  narrative "FIX (confirmed date, reviewer pass)" essays in source
  comments toward shorter, timeless doc-comments — full "why/when/who
  found it" story lives in commit messages and this roadmap's daily log
  instead. Documented as an explicit rule in `AGENTS.md`.
- [ ] **Production deployment process does not exist yet.** Confirmed:
  the project is still purely in local/dev-box development, with no build,
  package, or deploy step defined. This is the right time to design test-
  file exclusion from any deployable artifact (e.g. an `rsync --exclude`
  step, or `git archive` on a tagged release once modules are real,
  separate repos) directly into that process from day one — flagged as a
  real, open item to design once deployment planning begins, not solved
  yet.

## 12. Page Momentum (visible admin dashboard)

- [x] Routed `/admin/page-momentum` with real read-only facts
- [ ] **[R] Reviewer recommends deleting or radically shrinking this** —
  15+ test files/dozen classes for a static readiness page, vs. missing
  features like page delete. Explicit judgment call, not yet decided.

## 13. Consolidated "true-modular" roadmap (from reviewer passes)

1. Pick one canonical module-home convention (§1)
2. Extend the compile step beyond just the module list (§1, §15)
3. Real ALTER/removal support + FK declarations in the schema engine (§1, §2)
4. **[Substantially advanced]** Real security hardening:
   `EMULATE_PREPARES` ✅, pinned collation ✅, rate-limit salt ✅, sanitizer
   driver guard ✅, privilege-escalation fix ✅, race-condition fix ✅, 2FA
   key rotation ✅. Still open: real rate-limit *enforcement*, account
   lockout, password reset.
5. **[Done]** Fixed the `role.manage`/`user.manage` privilege boundary
6. **[Done]** Verified the obsolete parallel 2FA family is already retired
7. **[Done]** Shared presentation contracts moved to Core; Page and Settings dropped `zoosper/admin`
8. Consolidate the two Grid systems and two AdminForm systems into one
9. Standardize module naming; real semver constraints instead of `*@dev`
10. Add delete/archive to every admin CRUD screen
11. Enforce a public/internal API boundary between every pair of feature modules
12. CI pipeline gated on Pest, static analysis, architecture-boundary tests
13. Purge `tools/` to operational scripts only; resolve Page Momentum;
    remove AI-session tooling scripts from the repo (§0, §11)
14. **[Done]** Reconciled the `marko/framework` roadmap claim — root
    `composer.json` cleaned of every unused Marko package; real, verified
    adoption continues per-module (`zoosper/errors`, `zoosper/core`) — see §14
15. **[New]** Extract logger (`marko/log`/`marko/log-file`) as the next
    `zoosper-core` → standalone-package candidate — see §14

---

## Open questions for the next planning session

1. **Production deployment process design** — genuinely still to be
   designed (see §11's new item). Key open question once ready: how will
   code actually reach a production server (git-based deploy, build
   artifact/zip, something else) — the right test-exclusion mechanism
   depends entirely on the answer.
2. **`marko/database-readwrite`'s real dependency on `marko/database`'s
   connection interface** — needs the actual `ConnectionInterface`/
   `TransactionInterface` source read before any further read-replica
   design, now that the "fully decoupled from `marko/database`" assumption
   has been corrected (see §14).
3. Rate-limit enforcement timeline, given the high-traffic assumption
   sharpening urgency vs. the ADR's "collect real report-only data first"
   precondition.

---

## External API-backed grids — start after current Grid closure

> **Sequence gate:** Do not begin this programme until the current admin Grid is
> completely closed. Grid closure means explicit keyed headers are shipped,
> package/application runtime compatibility work is resolved, the full test
> suite is green, browser drag and live reflection are verified, and the Grid
> closure commit is deployed.

- [x] **Phase 4ZJ — Data-source boundary.** Establish shared
  `GridDataSourceInterface`, `GridQuery`, `GridResult` and
  `GridDataSourceCapabilities` contracts for database-backed and external
  collections. Support numbered and cursor pagination without coupling the
  generic Grid package to HTTP or a particular API response shape. Preserve
  existing Pages Grid behaviour while proving the renderer is data-source
  agnostic.

- [x] **Phase 4ZK — `zoosper-api-grid` transport and mapping kernel.** Add an
  installable adapter package containing request, response, transport,
  authentication, row-mapping, reliability and descriptive exception
  contracts. Test with fake transports first; do not embed Orders-specific
  logic in the master package.

- [x] **Phase 4ZL — Declarative registration and reusable page composition.**
  Add API Grid definitions, registry, data-source adapter and admin page builder
  so a feature module can register an API-backed Grid without writing a custom
  Grid controller. Render filters, sorting, search and export only when the
  remote source declares those capabilities.

- [x] **Phase 4ZM — Store Orders pilot.** Build `/admin/store-orders` as the
  first real consumer using remote page and page-size parameters plus trusted
  store and website scope. Reuse saved views, configurable columns, locked
  anchors, live ordering and controlled error presentation. Do not implement
  misleading current-page-only search, filtering or sorting.

- [ ] **Phase 4ZN — Hardening and second pilot.** Prove the abstraction with a
  materially different API envelope or pagination model. Cover invalid JSON,
  timeouts, non-success responses, schema drift, response-size limits, secret
  and personal-data redaction, bounded exports, diagnostics and cursor
  pagination where available.

- [ ] **Phase 4ZO — Developer experience.** After two integrations validate the
  contracts, add `bin/zoosper make:api-grid`, readable scaffolding, reusable
  fixtures, an example module, integration documentation and an upgrade policy.

### API Grid architectural boundaries

- `zoosper-grid` owns generic definitions, criteria, pagination and rendering.
- `zoosper-admin-grid` owns the admin workspace, bookmarks, preferences,
  filters, export policy and browser runtime.
- `zoosper-api-grid` adapts external collection APIs into existing Grid data
  source contracts; it does not introduce another Grid framework.
- Feature modules own endpoint-specific request, response and row mapping,
  permissions and trusted context such as store or website scope.
- API base URLs and credentials never come from browser query parameters,
  templates, bookmarks or Grid definitions.
- Remote failures must remain distinguishable from valid empty results, and
  external payloads containing personal or transactional data must not be
  dumped into logs, exceptions, list HTML or default exports.

### API Grid definition of success

A module developer supplies an endpoint definition, authentication strategy,
request mapper, response mapper, row mapper, Grid columns and permissions.
Zoosper supplies route integration, admin layout, pagination, capability-aware
controls, saved views, column visibility and ordering, live reflection,
controlled failures, export policy, tests and diagnostics.

## Daily log (most recent first)
- **2026-08-02 (Phase 5E)** — Completed the Store Orders live admin pilot with
  bounded read-only JSON transport, server-owned API/scope configuration,
  module-owned route/controller/ACL/menu wiring and a distinct 503 failure
  state. Live availability remains deployment configuration, not a test input.
- **2026-08-02 (Phase 5D)** — Added the Store Orders API Grid feature adapter:
  trusted-scope request mapping, strict response-envelope validation, privacy-
  minimised row mapping, capability declaration and Grid definition. Live HTTP
  transport and admin route wiring remain for the next pilot phase.
- **2026-08-02 (Phase 5C)** — Added immutable API Grid definitions, duplicate-safe
  registry, capability-constrained query creation and reusable page composition.
  Unsupported remote search, filters and sorting are removed before a data
  source receives the query. Store Orders remains the first real integration.
- **2026-08-02 (Phase 5B)** — Added `zoosper-api-grid` with replaceable
  read-only transport, request/response values, authentication, trusted context,
  reliability policy and request/response/row mapper contracts. Fake-transport
  tests prove external failures remain distinct from empty Grid results.
- **2026-08-02 (Phase 5A / API Grid foundation)** — Added the transport-neutral
  Grid data-source boundary with immutable query, result, capability and
  numbered/cursor pagination contracts. No HTTP, API envelope or Orders logic
  entered `zoosper-grid`; `zoosper-api-grid` remains the next adapter layer.
- **2026-08-02 (Phase 4ZJ Grid closure)** — Closed the current admin Grid asset
  ownership boundary: `zoosper-admin-grid` now serves its canonical column
  ordering runtime and stylesheet directly through the secured module asset
  route. The application compatibility copies, registrations and parity-only
  tests were removed. Content-derived versions and direct resolver coverage are
  enforced. The external API-backed Grid programme may begin after deployment
  and final browser verification.
- **2026-08-02 (planned after Grid closure)** — Scheduled the external
  API-backed Grid programme. The sequence begins with a generic data-source
  boundary, then `zoosper-api-grid`, reusable API Grid page composition, Store
  Orders as the first pilot, a second materially different pilot, and finally
  scaffolding and developer documentation. No API Grid implementation begins
  until the current admin Grid closure gate is satisfied.
- **2026-08-02 (Phase 4ZI)** — Completed the explicit grid-column identity
  contract: all Grid table header branches now emit escaped
  `data-grid-column` keys, positional browser inference was removed, package and
  compatibility runtimes remain parity-guarded, and the browser asset digest is
  refreshed from the new runtime content.
- **2026-08-02 (Phase 4ZH)** — Consolidated column drag and live reflection into
  one package-owned runtime with a parity-guarded application bridge, keyboard
  movement, idempotent binding, content-derived asset versions, and JavaScript
  syntax checks. Server-rendered header keys and direct package asset routing
  remain the next closure steps.
- **2026-08-02** — Completed the Pages admin grid workspace visual cutover:
  configurable visibility, page-size selection, filtering, CSV export,
  bookmark persistence, draggable column ordering with locked ID/Actions
  anchors, and immediate live grid reflection. The closure review identified
  duplicate asset ownership, missing content-based cache busting, runtime
  header-key repair, and overly source-oriented JavaScript tests. The next
  phase is consolidation and behavioural verification, not new feature work.

- **2026-07-31** — Cleaned up root `composer.json`: removed all 7
  transitively-unused Marko packages (`marko/database`,
  `marko/database-mysql`, `marko/framework`, and framework's own unused
  transitive deps `marko/cli`/`marko/hashing`/`marko/routing`/
  `marko/validation`), confirmed via a real `composer update` with zero
  breakage. Every Marko package Zoosper actually uses is now a direct
  dependency of the specific module that uses it, not the root project.
  Built the real cache foundation: `Zoosper\Core\Cache\CacheDriverFactory`
  (file or Redis, one config flag) and
  `Zoosper\Core\Config\MarkoConfigRepositoryAdapter` (resolving a real
  circular-dependency concern by keeping the adapter inside `zoosper-core`
  rather than a new package). Built the actual page-cache consumer:
  `Zoosper\Core\Routing\CachingFallbackHandler`, opt-in and disabled by
  default, found and fixed a real cache-key-format incompatibility with
  Marko's own validation while building it. Added `.gitattributes` to both
  extracted packages, matching Marko's own real convention. Refreshed
  README, AGENTS, SECURITY, and `.env.example` for accuracy; corrected an
  over-optimistic earlier claim about `marko/database-readwrite` being
  fully decoupled from `marko/database`.
- **2026-07-30** — Major session: fixed both 2026-07-29 top-priority items
  (privilege escalation, rate-limit race condition). Fixed a live
  production incident (2FA key rotation causing a real admin lockout) with
  full key-rotation support and opportunistic re-encryption. Fixed
  `EMULATE_PREPARES`, rate-limit salt, HTML sanitizer driver guard, `Pager`
  unbounded page, schema collation, `Request::form()` immutability,
  4 confirmed `bootstrap/autoload.php` bugs, PDO-before-error-handler boot
  order, grid `sortBy` being ignored, `MediaUploadServiceResult` typing.
  Added LICENSE + SECURITY.md. Built the module-manifest compile step
  (`compile`/`cache:clear`/`deploy`), then fixed a real bug it introduced
  (migrations against a stale cached module list) once caught by a second
  reviewer pass. Extracted `zoosper/errors` as the first standalone
  package out of `zoosper-core`, with real (not just installed-but-unused)
  Marko framework integration.
- **2026-07-29 (reviews)** — Received two review passes (Fable pass #1,
  Sonnet). Logged findings, flagged privilege escalation and race
  condition as top priority. No code changes that day — deferred to
  2026-07-30.
- **2026-07-29 (build)** — Wired report-only rate limiting onto
  `/admin/login`. Fixed `MediaAdminController::upload()` swallowing
  failures. Fixed non-atomic writes in `RoleRepository`/`AdminUserRepository`.
- **2026-07-29 (earlier)** / **2026-07-25** — See prior roadmap versions
  for full detail (Phase 1.107 2FA enforcement, Phase 1.40/1.40c/1.41
  module packaging and decoupling, frontend boot fixes, security headers
  batch).

> Add a new dated bullet each day at wrap-up. Keep bullets to one or two lines;
> the checklists above are the durable state. This file is the project's
> actual continuity mechanism — keep it honest and current.


- [x] Environment bootstrap ownership consolidated in Phase 2D; duplicate class loaders retired.

### Phase 5I: Store Orders Grid closure (completed)

- Completed the external API Grid pilot with CSRF-protected server-side column preferences and saved-view lifecycle.
- Store Orders now persists visible columns, bookmarks, default views, filters, remote page size and column order through shared Admin Grid contracts.
- Bookmark normalisation safely removes retired keys and merges newly declared columns.
- Remote sorting and export remain disabled until Node exposes explicit contracts.
- The next visible admin UX phase is the modern searchable Permission Explorer.
## Priority after API Grid closure: modern Settings platform

**Sequence gate:** complete and close the API Grid programme before beginning Settings implementation. Once API Grid is closed, Settings becomes the highest-priority product and architecture programme before unrelated new features.

### Why this moves ahead of new features

Existing and future features should consume declared, validated and scoped configuration rather than adding hard-coded values or unrelated environment variables. The Settings platform must be designed as shared infrastructure, not as a single hand-built admin screen.

### Settings programme

- **Phase S0 — inventory and ownership audit.** Catalogue hard-coded behaviour, root/module config, environment-only values, database-backed settings, secrets and per-admin preferences. Classify each value as build-time, environment, secret, deployable site configuration, runtime operational state or user preference.
- **Phase S1 — typed settings contracts.** Add module-owned setting definitions, groups, field types, defaults, validation, normalisation, visibility rules, descriptions, ownership and deprecation metadata. Definitions remain code; editable non-secret values are stored separately.
- **Phase S2 — scope and resolution.** Implement deterministic precedence for platform, environment, site, store/channel and user scopes. Expose resolved-value provenance so administrators can see whether a value is inherited, overridden or locked.
- **Phase S3 — secure persistence and audit.** Add atomic writes, CSRF and permission enforcement, optimistic concurrency, secret references rather than plaintext secrets, redacted audit history, rollback and cache invalidation.
- **Phase S4 — modern admin workspace.** Build searchable categories, deep links, unsaved-change protection, validation summaries, inherited-value indicators, reset-to-inherited controls, documentation links and accessible field rendering.
- **Phase S5 — module extensibility and API.** Let modules contribute setting groups and processors without editing core. Provide typed read APIs for application code and controlled write APIs for authorised automation.
- **Phase S6 — configuration portability.** Add reviewable import/export and environment promotion for deployable configuration while keeping secrets, local operational state and personal preferences out of portable bundles.
- **Phase S7 — hard-coded configuration migration.** Move appropriate existing constants and ad hoc values behind the resolver in small, tested batches. Do not migrate database credentials, encryption keys or other bootstrap secrets into editable admin settings.
- **Phase S8 — closure and developer experience.** Add scaffolding, examples, diagnostics, upgrade/deprecation policy, full documentation and behavioural coverage.

### Non-negotiable boundaries

- Secrets are referenced from environment or a secret provider and are never returned to the browser after storage.
- Definitions, values, secrets, operational state and user preferences are distinct concepts and stores.
- Module defaults sit below explicit deployment and runtime overrides.
- Every editable value has a type, validator, permission, owner and audit policy.
- Settings reads use one resolver rather than direct database, `$_ENV`, `getenv()` or scattered config-file access in feature code.
- Runtime changes must not silently overwrite deployable configuration.
- Unknown, retired and incompatible keys fail descriptively during import.

- **Phase 7B — API Grid reliability hardening source capture.** Capture the exact Grid/API Grid/Store Orders contracts and reliability seams before implementing second-pilot hardening; do not invent a production endpoint or credentials.

## Phase 9FL closure note

Media Admin implementation decoupling, Page Admin layout/view interface adoption and frontend structured-content rendering are complete and regression-guarded. Page now declares its Media runtime dependency honestly. Remaining Page work concerns narrower editor/form/Grid ownership cleanup and visible product improvements, not reimplementation of these completed foundations.

## Phase 9FM route-parameter cutover

Page edit, preview, publish and unpublish actions now expose constrained parameterised routes and resolve the Page identifier from immutable request route parameters. Existing query-string routes remain temporarily available for backwards compatibility, while newly generated Grid and edit-form links use canonical path URLs.

## Phase 9FN save and publication closure

Page create/update normalisation, extensible form processing, entity-save lifecycle execution and persistence now run through a Page-owned save coordinator. Single-Page publish/unpublish mutations and events run through a Page-owned publication coordinator. The Admin controller remains responsible for HTTP responses, flash presentation and redirects.

## Phase 9FO form and request-query closure

Page Admin form context, Site options, content-editor fallback, SEO fields, structured-content fields, CSRF token and extensible `page.form` section rendering now live in a Page-owned form renderer. Page Grid reads query state from the immutable Request query map rather than `$_GET`.

## Phase 9FP final Page Admin controller closure

Pages Grid screen composition and protected Grid mutations now run through a Page-owned Grid responder. Read-only Admin preview resolution now runs through a Page-owned preview responder while retaining the single `PageRenderer` path. `PageAdminController` is reduced to authentication, entity lookup, delegation, flash/redirect selection and HTTP response selection.

## Phase 9FQ Page dependency-honesty closure

Page runtime cleanup is complete. `zoosper/admin` remains an explicit and justified dependency for `ContentEditorInterface`, `FlashMessageStoreInterface`, and `AdminFormConfigAggregator`. Future removal requires a coordinated cross-module contract migration and is not part of the completed Page thin-controller arc.

## Phase 9FR shared presentation-contract migration

Shared content-editor and flash-message interfaces plus Admin form configuration aggregation now belong to Core. Admin retains concrete editor and session-backed flash implementations. Page and Settings no longer require `zoosper/admin`, completing their package-boundary migration.

## Phase 9FS Page Grid dead-runtime closure

Superseded complete-page and framework-neutral controller scaffolding was removed after repository-wide reference analysis proved it was isolated to self-tests. The deployed Page Grid remains owned by `PageAdminGridResponder`, `PageGridWorkspace`, and `PageGridMutationCoordinator`.
## Phase 9FT Settings Admin runtime closure

The Settings controller is now a thin authenticated HTTP adapter. Settings-owned collaborators handle catalogue rendering, scoped save and clear workflows, audit logging, flash messages and canonical URLs without changing the existing routes or template contract.

## Phase 9FU Settings presentation asset closure

The Settings workspace stylesheet and browser runtime are now module-owned assets served through the secured asset route and registered through the Admin asset manifest. The template retains semantic markup and its JSON bootstrap payload but no executable inline CSS or JavaScript.

## Phase 9FV Settings presentation test consolidation

Settings Admin presentation-contract tests now load the semantic template, stylesheet and browser runtime through one guarded test helper. Behavioural test files remain focused while repeated three-file setup introduced during the asset cutover is retired.

## Phase 9FW Settings presentation model closure

Category ordering, module options, section search metadata, field editability, input metadata, display values and scope bootstrap JSON now come from one Settings-owned presentation builder.

## Phase 9FX Settings composition closure

Settings presentation, scope-selection and canonical-URL collaborators are registered through the module service manifest and resolved by controller wiring. Direct construction of these shared Settings composition services is regression-guarded.

## Phase 9FY Settings persistence contract closure

Settings scoped reads, atomic writes and clear operations now use a Settings-owned persistence contract backed by a Core scope-config adapter. Application services no longer depend directly on PDO or ScopeConfigRepository.

## Phase 9FZ Media upload runtime composition closure

Confirmed and fixed the shared root cause behind private upload orchestration: both production upload controllers now resolve the registered `MediaUploadService`, so explicit cleanup wiring is used consistently. Derivative processing remains an explicit follow-up because no production processor and enablement policy are registered yet.

## Phase 9GA CSP and roadmap truth closure

Reset 2FA confirmation is now provided by a registered Auth JavaScript asset with no inline event handler. Roadmap claims now match the verified 2FA, module-discovery, Page/Settings dependency, asset-containment and Media composition states.

## Phase 9GB CI quality-gate closure

The `dev` branch now has a least-privilege, concurrency-controlled PHP 8.5 workflow with Composer caching, strict validation and audit, JavaScript checks, strict gate, full Pest and compile enforcement. Psalm records its existing static-analysis debt as an explicit advisory signal rather than falsely blocking all changes.

## Phase 9GC Runtime boundary hardening

Cross-layer module identity collisions now fail descriptively instead of allowing a stale higher-priority copy to shadow an extracted package. Same-layer duplicate handling remains intact. Asset resolution gained explicit null-byte, encoded traversal and symlink-escape regression coverage while retaining valid asset behaviour.

## Phase 9GD HTTP exception presentation closure

Exceptions caught by Router and Application now use one environment-aware ErrorHandler boundary. Development web requests render Marko HTML from `zoosper-errors`; production web requests and all API requests stay generic. Both catch boundaries retain single-shot logging, HTTP status and content type.

## Phase 9GE CLI recovery bootstrap closure

The console already had the correct shared configuration loader and a lazy PDO provider; Phase 9GE completed the boundary by carrying the provider into module-command composition, registering PDO lazily, migrating the stale root-only test fixture and adding real subprocess coverage proving recovery commands operate with an unreachable database.

## Phase 9GF Console kernel decomposition

Five operational commands moved into Core-owned command classes, reusable console service composition and kernel boundaries were introduced, and `bin/zoosper` stopped owning migration, compilation, cache-clear and manifest diagnostic implementations. Deployment and scaffolding extraction remain the next bulk slice.

## Phase 9GG Console deployment and scaffolding extraction

Deployment and all three scaffolding workflows moved into Core-owned command objects. `bin/zoosper` no longer owns deploy sequencing, scaffold output or local option helpers; the executable is now limited to bootstrapping, command composition and dispatch.

## Phase 9GH Admin asset manifest compatibility and Settings runtime recovery

`AdminAssetRegistry` now supports both the canonical wrapped `assets` manifest and the established flat module manifests used by Settings and Auth. Settings workspace CSS and JavaScript are again discoverable, restoring module-owned layout, print-only visibility rules and interactive workspace behaviour without moving feature styles into global Admin CSS.

## Phase 9GI Alpha release-candidate readiness strike

Zoosper now owns an authoritative `0.1.0-alpha.1` version, CLI `version` and `release:check` commands, an operator documentation set, an alpha checklist, changelog and a CI release-readiness step. Fresh-install and browser smoke evidence remain checklist requirements before tagging the alpha.

## Phase 9GJ Fresh-install and runtime smoke closure

CI now proves a disposable SQLite install from zero, idempotent migration reruns, initial Admin and site bootstrap, password hashing, expected core tables, duplicate-admin failure semantics, critical route inventory and critical Admin assets. Live authenticated browser and frontend rendering checks remain explicit pre-tag manual evidence.

## Phase 9GJ release-version parity hotfix

Admin presentation and application configuration now default to the authoritative `config/version.php` release identity. `CMS_VERSION` remains an optional explicit deployment override; stale development defaults were removed.

## API health release-version parity hotfix

The public health payload now reads the authoritative release identity from `config/version.php`; the stale `0.3.0-dev` literal was removed.

## Post-release 0.2 development line

After tagging `v0.1.0-alpha.1`, the central development version advances to `0.2.0-alpha.1-dev`. The 0.2 line prioritises useful CMS core and visible product momentum while preserving the release gates established for 0.1.

## Phase 9GK Page revision domain foundation

Page revisions now support full restorable snapshots, Page-scoped lookup and bounded retention. The next adoption slice wires history, preview and restore into Page Admin with audit logging.

## Phase 9GLA Page Momentum retirement

The temporary `/admin/page-momentum` engineering dashboard was removed from production. Its route, menu/config fragments, controllers, providers, template and dedicated architecture tests were retired. Release readiness remains enforced by CI, fresh-install smoke, release checks and focused Page feature tests.

## Repository hygiene

Canonical documentation now lives in `docs/`; module and package directories retain only concise independently useful READMEs. Executable tooling consolidation is the next separate hygiene stream and must preserve Composer, CI, hook and strict-gate dependencies.

## Repository hygiene baseline

Repository cleanup is complete at the documentation and tooling layers. Active root tools are limited to operational, verification, gate and developer utilities; completed apply scripts and package-local migration scaffolding are removed after adoption.

## Module ecosystem interoperability

Zoosper exposes one public module identity: `type: zoosper-module`. A central private classifier recognises upstream Marko modules so Zoosper can compose over maintained Marko capabilities without publishing a second authoring contract.

## Page revision Admin adoption

Page saves now use complete retained snapshots. Page edit exposes revision history, historical preview and restore; restore first captures the current state and records an audit event.

## Documentation website

A zero-dependency PHP static builder now consumes canonical `docs/`, validates internal links and exports `docs-site/build/` for hosting at `docs.zoosper.com`.

## Shared product branding

The Theme module now owns the canonical Zoosper mark. Admin, the default frontend theme and the static documentation build consume published copies through stable asset contracts; custom frontend themes remain free to override presentation.

## External review response and public-launch priorities (2026-08-11)

The external senior-engineer review of commit `f4e93935fb17bf86c3126c44315453cfe1bf722a` was accepted as a launch-readiness input. Verified closures remain closed; roadmap summary lines must agree with detailed phase notes and current source.

### P0 before public announcement

- [ ] Define and ship consistent archive/delete lifecycle behaviour for Pages, Admin Users, Roles, Sites, Site Domains, and Media. Menu delete is the current reference for POST, permission, and CSRF shape, but hard delete is not appropriate for every entity.
- [ ] Add declarative-schema foreign-key support or an equivalent shared restrict/cascade safety layer in the same delivery arc as broader entity deletion.
- [x] Update `SECURITY.md` to identify `v0.1.0-alpha.1` as the latest tagged pre-release while stating that no stable release has shipped.
- [x] Rewrite the root README current-state and included-capabilities sections for the alpha release, CI, Menu, Page revisions, docs site, Marko adoption, and explicit launch blockers.

### P1 before stable

- [ ] Emit an explicit diagnostic for every cross-layer module identity override before further package extraction.
- [ ] Either wire a production Media derivative processor and enablement policy or remove/de-scope the inactive processing surface.
- [ ] Consolidate the two Grid systems and adopt the extensible Grid model across remaining eligible Admin screens.
- [ ] Consolidate the two Admin Form systems and adopt the section/processor model across remaining forms.
- [ ] Graduate Admin login rate limiting from report-only observation to an enforced, tested policy.
- [ ] Add password policy and `password_needs_rehash()` upgrade support.
- [ ] Decide and document the CSRF model for stateful session-based `/api/*` routes.
- [ ] Fail closed in production when secure-session configuration is absent or unsafe.
- [ ] Investigate and consolidate environment loading across `Core\Bootstrap\EnvLoader`, `Core\Env`, and the global `env()` helper.
- [ ] Register the project error handler before module discovery and module configuration execution.

### P2 process and hygiene

- [ ] Add a mechanical roadmap-summary drift check based on structured status identifiers, not fuzzy prose matching.
- [ ] Commit a Psalm baseline and prevent new advisory errors while reducing the existing baseline.
- [x] Make `composer.json` and `composer.lock` the source of truth for the dependency scope stated in `SECURITY.md`.
- [ ] Record behavioural assertion/test coverage evidence for the historical test-file reduction.
- [ ] Define semver constraints for extracted first-party packages instead of publishing `dev-dev` as the only compatibility signal.

### Deferred while launch blockers are active

- [ ] Resume Phase 9HF Marko dashboard widget adoption after the P0 lifecycle and integrity foundation is underway.

## Phase 9HH — shared entity lifecycle policy foundation (2026-08-11)

- [x] Added shared archive, disable, and permanent-delete operation vocabulary.
- [x] Added immutable lifecycle subjects, descriptive blockers, allow/deny decisions, a loud policy registry, a read-only decision service, and a denial exception carrying the full decision.
- [x] Enforced one policy per entity type and rejected missing, duplicate, and dishonest policy results.
- [x] Kept mutation executors, controllers, persistence changes, and database foreign keys out of this foundation phase.
- [ ] Next: audit Page repositories, revisions, menus, and URL rewrites, then implement the first real Page archive/delete policy and executor alongside referential-integrity work.

## Phase 9HI — declarative foreign-key schema foundation (2026-08-11)

- [x] Added typed, named declarative foreign keys with local/reference columns and explicit update/delete actions.
- [x] Defaulted foreign-key actions to `RESTRICT`; supported explicit `CASCADE`, `SET NULL`, and `NO ACTION`.
- [x] Added loader, same-table registry merge, cross-table validation, identifier safety, and fresh MySQL/SQLite CREATE TABLE SQL.
- [x] Added real SQLite enforcement coverage for restrict, cascade, and set-null behaviour.
- [x] Kept existing-table reconciliation out of this phase; no SQLite table is rebuilt implicitly.
- [ ] Next Phase 9HJ: add foreign-key inspection, MySQL ALTER planning, explicit SQLite rebuild diagnostics, and idempotent existing-database reconciliation.
- [ ] Following Phase 9HK: adopt the lifecycle foundation for Page archive, restore, and guarded permanent delete.

## Phase 9HJ — existing-database foreign-key reconciliation planning (2026-08-12)

- [x] Added driver-aware live foreign-key inspection for MySQL `INFORMATION_SCHEMA` and SQLite `PRAGMA foreign_key_list`.
- [x] Normalised local columns, referenced columns, target table, and update/delete actions into comparable state objects.
- [x] Added read-only reconciliation outcomes: present, MySQL add, mismatch, and explicit SQLite rebuild required.
- [x] Added deterministic MySQL `ALTER TABLE ... ADD CONSTRAINT` SQL generation.
- [x] Prohibited automatic SQLite foreign-key alteration or table rebuild.
- [x] Added real SQLite tests for equivalent existing constraints and missing-constraint rebuild diagnostics.
- [ ] Next: add an explicit operator-facing schema reconciliation command/apply boundary with snapshot recording and dry-run output.
- [ ] Following: declare the first production Page relationships and ship Page archive, restore, and guarded permanent delete.

## Phase 9HK — foreign-key operator boundary (2026-08-12)

- [x] Added `schema:foreign-keys:status` with text and JSON output over live declarative reconciliation plans.
- [x] Added confirmation-gated `schema:foreign-keys:apply` and `--dry-run=1` status delegation.
- [x] Limited automatic application to safe missing MySQL constraints; mismatches and SQLite rebuild requirements block the operation.
- [x] Recorded successful apply statements through `SchemaSnapshotRepository`, including successful statements before a partial DDL failure.
- [x] Kept PDO lazy through operational command factories and preserved database-free help, compile, and cache-clear recovery paths.
- [x] Kept ordinary `migrate` independent from existing-table foreign-key application.
- [ ] Next: declare and reconcile the first production Page relationships, then ship Page archive, restore, and guarded permanent delete.

## Phase 9HL — Page lifecycle domain and integrity adoption (2026-08-12)
- [x] Added Page-owned archive, restore-to-draft, and guarded permanent-delete coordination.
- [x] Captured complete revisions before archive and restore.
- [x] Blocked permanent deletion unless archived and free of Menu-item and Page URL-rewrite references.
- [x] Added transactional revision/Page deletion and a declarative `page_revisions.page_id` CASCADE relationship.
- [x] Declared the migration-owned Page and revision identity columns required for standalone declarative validation and fresh-install schema merging.
- [x] Added real SQLite lifecycle and reference-blocker coverage.
- [ ] Next: expose POST-only Admin archive/restore/delete routes and CSP-safe destructive presentation through the existing thin Page controller.

## Phase 9HM — Page lifecycle Admin adoption (2026-08-12)
- [x] Added POST-only `page.manage` routes for archive, restore, and guarded permanent deletion.
- [x] Kept the Page controller thin by delegating lifecycle execution and presentation to a Page-owned responder.
- [x] Added contextual edit-screen forms with central CSRF tokens and a separated destructive action.
- [x] Added structured flash feedback for status and reference blockers.
- [x] Added archived Pages to the Grid status filter.
- [x] Kept permanent delete out of ordinary Grid row actions and removed the need for inline confirmation handlers.
- [ ] Next: remove stale Page Momentum asset registration and migrate the revision restore confirmation to an explicit CSP-safe interaction.

## Phase 9HN — Page editor compactness and revision pagination (2026-08-12)
- [x] Added repository-level Page revision count and `LIMIT` / `OFFSET` paging.
- [x] Rendered revision history as a compact native disclosure with ten revisions per page.
- [x] Removed the inline restore confirmation and retained POST, CSRF, preview, audit, and safety-snapshot protections.
- [x] Removed duplicate Content-section guidance and kept editor-specific messaging in the editor component.
- [ ] Parked visual/HTML mode switching and richer Editor.js formatting tools for a dedicated editor-contract phase.
- [ ] Continue reviewer-priority referential-integrity and lifecycle adoption across Site, Menu, Role, Admin User, and Media entities.

## Phase 9HO — asynchronous Page revision pagination (2026-08-12)
- [x] Added a protected Page revision fragment endpoint under the existing `page.manage` route boundary.
- [x] Added progressively enhanced Previous/Next requests that replace only the revision table and pager.
- [x] Preserved ordinary links as a no-JavaScript and request-failure fallback.
- [x] Updated browser history with `revision_page` without reloading the Page editor.

## Phases 9HP-9HR — reviewer closure bulk (2026-08-12)
- [x] Retired obsolete Page Momentum Admin menu and stylesheet registrations.
- [x] Added tenant-wide real-registry Admin asset manifest validation.
- [x] Mirrored migration-owned Site Domain, Menu, Menu Item, Admin User Role, and Admin Role Permission foreign keys in declarative schema.
- [x] Added drift tests for destructive actions and relationship semantics.
- [ ] Next: apply shared lifecycle policy adoption only to entities whose current status and reference contracts support it without inventing deletion semantics.

## Phases 9HS-9HV — guarded Site lifecycle (2026-08-12)
- [x] Added read-only Site reference inspection across Domains, direct Pages, Page assignments, Menus, and URL Rewrites.
- [x] Added inactive/restore workflow using the existing Site status vocabulary.
- [x] Added inactive-first, fully unreferenced permanent deletion with transaction and audit boundaries.
- [x] Added protected `settings.manage` POST routes, contextual edit-screen actions, thin-controller delegation, and regression tests.

## Phases 9HW-9HZ — guarded Menu lifecycle (2026-08-12)
- [x] Added Menu item ownership and hierarchy reference inspection.
- [x] Added inactive and restore workflow using the existing Menu status vocabulary.
- [x] Replaced direct whole-Menu deletion with inactive-first, empty-Menu permanent deletion.
- [x] Hardened Menu item deletion against cross-Menu and parent-cascade removal.
- [x] Added contextual CSP-safe lifecycle actions and a compact native disclosure workspace.

### Phases 9IA-9ID — Admin identity lifecycle and password security (2026-08-12)
- Added reversible Admin User inactive/restore lifecycle, protecting the current account and the last active super administrator.
- Added guarded deletion for unassigned custom Roles while protecting `super_admin`.
- Added POST-only, permission-gated, centrally CSRF-protected identity lifecycle routes and contextual Admin actions.
- Adopted the existing canonical Admin password policy through configuration-backed HTTP and `admin:create` wiring, and verified the already-present successful-login hash rehash support.
- Kept permanent Admin User deletion unavailable so identity, audit, login-history, and ownership attribution remain intact.


### Phase 9IE — Zoosper Session adapter with Marko file storage (2026-08-12)
- Added the native `zoosper/session` module as the ownership and replacement boundary for third-party session infrastructure.
- The module requires and adapts `marko/session-file` 0.8.5; the root project requires only `zoosper/session`.
- Core resolves only native `SessionHandlerInterface`, keeping Marko implementation details outside `zoosper-core`.
- Session payloads now default to application-owned `var/sessions`, while configured lifetime, Admin idle timeout, CSRF, flash, 2FA, regeneration, and logout semantics remain intact.

### Phase 9IF Media lifecycle truth closure
Media assets now use POST-only, media.manage, CSRF-protected archive, restore and archived-first permanent deletion boundaries. Metadata deletion is transactional and owned-file cleanup is conservative and audited. Upload derivatives remain disabled by default; LocalCopyMediaProcessor is not image transformation support.

### Phase 9IG-9IJ — starter experience and alpha readiness (2026-08-12)
- Added an idempotent `starter:install` command that retains existing content and creates only a missing Site plus published Home/About Pages.
- Confirmed the existing default theme as the 0.2 starter-theme foundation.
- Expanded release diagnostics to require starter-theme assets, the starter command, and application-owned session settings.
- Release identity remains `0.2.0-alpha.1-dev` until final rehearsal and manual browser evidence are complete.

### v0.2.0-alpha.1 release candidate (2026-08-12)
- Closed the useful-CMS-core alpha scope around guarded Menus and Media, Page revisions, CLI polish, idempotent starter content, the default starter theme, and application-owned sessions.
- Final release identity is `0.2.0-alpha.1`; production deployment policy, distributed Redis/database session drivers, enforced rate limiting, and stable compatibility promises remain future work.
- Tag only after the committed release identity has passed the full suite, fresh-install smoke, release checks, documentation build, manual browser acceptance, and a clean working tree.

### v0.3.0-alpha.1 development line opened (2026-08-12)
- Advanced the authoritative development identity to `0.3.0-alpha.1-dev` after the verified `v0.2.0-alpha.1` tag.
- The release theme is Content and Marketing: SEO presentation first, followed by sitemap/robots, redirect management, forms, and focused block-editor improvements.
- Existing 0.2 release gates remain mandatory; unrelated distributed-session, data-layer, commerce, localisation, and ecosystem programmes remain outside this release arc.

### Phase 10A-10D — SEO presentation foundation (2026-08-12)
- Added engine-neutral Page SEO resolution and equivalent escaped PHP/Latte head output for title, description, robots, canonical and Open Graph basics.
- Explicit absolute Page canonicals take precedence; safe Site-base derivation is available for published request rendering, while previews remain noindex.
- Sitemap, robots endpoints, redirect management and Site-level SEO defaults remain following 0.3 slices.

### Menu item update PDO parity hotfix (2026-08-12)
- Fixed the live Menu item update path after exact-source diagnosis showed an insert-only `created_at` parameter was supplied to update SQL, causing PDO HY093.
- Added a database-backed update regression before resuming Phase 10E-10H sitemap and robots.

### Phase 10E-10H — sitemap and robots foundation (2026-08-12)
- Added Site-scoped published Page discovery, deterministic XML sitemap output and a plain-text robots endpoint.
- Absolute public URLs require validated Page canonicals or Site base URL; request-host synthesis is intentionally forbidden.
- Redirect management and Site-level SEO defaults remain following 0.3 slices.

### Phase 10M-10P — dedicated SEO module and contributor discovery (2026-08-12)
- Corrected SEO ownership by extracting generic metadata, contributor discovery, sitemap and robots orchestration into `zoosper-seo`.
- Added module-discovered `config/seo.php` contribution so future modules can inject metadata and sitemap resources without SEO importing concrete Page types.
- Preserved public endpoints and theme layout data; URL rewrite work resumes after this boundary correction.

### Phase 10Q-10T — redirect domain and stateless SEO traffic (2026-08-12)
- Extended `zoosper-url-rewrite` with redirect validation and Site-scoped persistence foundations.
- Prevented `/sitemap.xml` and `/robots.txt` from starting application sessions.
- Frontend resolution, chain diagnostics, and guarded Admin management remain the next URL Rewrite slice.

### Phase 10U-10V — URL Rewrite frontend adoption and chain diagnostics (2026-08-13)
- Added a generic module-discovered service decorator stage, then used it to place Site-scoped URL Rewrite resolution ahead of the existing cached Page fallback without Page importing URL Rewrite.
- Added deterministic redirect cycle and maximum-depth diagnostics. Guarded Admin management remains pending permission and audit-policy closure.

### Security P0 API authentication closure (2026-08-14)
- Closed password-only API session promotion for 2FA-enrolled Admin accounts with an Auth-owned fail-closed second-factor contract implemented by the Two Factor module.
- Applied the canonical password-login limiter directly to API login and documented enforce-mode defaults. A dedicated API 2FA challenge or token protocol remains a separate design decision.

### Security P1 media ingest hardening (2026-08-14)
- Added fail-closed GD raster decoding and canonical re-encoding before private storage and public publication.
- Added a 40-megapixel safety ceiling and explicit single-frame GIF policy. Real responsive derivatives remain a separate performance phase.

### Phase 10AD media derivative runtime adoption (2026-08-14)
- Replaced the inactive copy-only upload seam with an enabled, interface-owned GD processor producing bounded WebP thumb, medium, and large derivatives.
- Added no-upscale contain/width behaviour, guarded cover crops, alpha preservation, atomic private/public writes, and upload rollback on derivative failure. Derivative database persistence remains a separate follow-up.

### Phase 10AG-10AI media derivative persistence (2026-08-14)
- Added declarative derivative records, upload-time metadata persistence, stable profile lookup, cascade ownership, and permanent-delete file cleanup.
- Regeneration/backfill CLI remains the next operational slice.

### Phase 10AK HTTP gateway foundation (2026-08-15)
- Added route-owned statelessness, 404/405 distinction with Allow, HEAD/OPTIONS handling, exact-origin CORS and production-secure session/rate-limit defaults.

### Phase 10AL-A Personal Access Token foundation (2026-08-15)
- Added Auth-owned PAT persistence, issuance, revocation, scope catalogue and stateless bearer identity. Admin token management and scoped mutation middleware continue in 10AL-B.

### Phase 10AL-B PAT Admin lifecycle (2026-08-15)
- Added self-owned Admin PAT issuance, one-time display, listing, revocation and safe audit events. Scoped bearer middleware adoption continues with API verticals.

- Phase 10AM-A: PAT-scoped Page API list/detail read vertical delivered.

- Phase 10AM-B: shared Page mutation application service plus PAT create/update delivered.

### Phase 10BL: Audit Log and Login History Admin Grid cutover

Audit Log and Login History now use the persistent `zoosper/admin-grid` workspace on top of their existing `zoosper/grid` definitions and repositories. The cutover adds per-admin saved views, column visibility, column ordering, compact filters, page-size state and direct navigation while preserving module-contributed columns such as Two Factor User Agent. The obsolete direct-controller `GridHtmlRenderer` path is retired; `zoosper/grid` remains the generic engine and `zoosper/admin-grid` remains the Admin workspace layer.

### Phase 10BM-A: remaining tabular Admin collections

Access Tokens, Sites, Site Domains and the Menus collection index use persistent Admin Grid workspaces and server-side pagination. PAT ownership and POST revocation remain unchanged; Site lifecycle remains POST-only; the individual Menu editor remains a specialised tree. Media follows as a paginated visual-grid workspace.
