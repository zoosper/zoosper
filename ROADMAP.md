# Zoosper CMS — Master Roadmap

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

**Last updated:** 2026-07-31 (Sydney)
**Framework baseline:** PHP 8.5 · Pest/PHPUnit · Psalm · Latte · **Marko packages
(real, adopted usage — see §14) via zoosper/errors and zoosper/core**

Legend: `[x]` done & deployed · `[~]` in progress / partial · `[ ]` planned
`[R]` reported by external reviewer (Fable pass #1, 2026-07-29), verification status noted inline
`[R2]` reported by external reviewer (Fable pass #2, 2026-07-30), verification status noted inline
`[FIXED]` confirmed fixed and deployed this session

---

## Architecture direction: Marko framework boundary (2026-07-31)

- **[DECIDED] Marko is the framework; Zoosper is the CMS product.** Generic
  framework mechanics must use Marko when an adequate package exists. Zoosper
  owns CMS domains, policy, workflows and presentation.
- **[DONE] Phase 1A architecture audit.** Added the Marko adoption matrix,
  framework/module/configuration/CLI ADRs, and first-party package ownership
  inventory under `docs/architecture/`.
- **[DONE] Phase 1B configuration consolidation.** HTTP, `bin/zoosper` and
  `bin/zoosper-schema` now share `ApplicationConfigLoader`; module defaults are
  layered beneath root overrides, and the container exposes Marko's canonical
  configuration contract through the migration adapter.
- **Next implementation:** Marko CLI ownership and lazy dependency resolution.
  Database-free recovery commands must not construct PDO.

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

- **[R2] Every CLI command requires a live database**, even `help`,
  `compile`, `cache:clear` (a *recovery* tool, unavailable exactly when
  most needed) — confirmed present in `bin/zoosper`'s structure since
  before this session's involvement; not yet fixed. Fix: connect to the DB
  lazily, only for commands that actually need it. **Relevant Marko
  package to check first, per this project's own "check Marko's catalog
  before building" rule**: `marko/cli`.
- **[R2] CLI and HTTP read different configuration** — web boot aggregates
  config via `ModuleConfigAggregator` (module configs + root); `bin/zoosper`
  uses `ConfigRepository::fromPath()` (root `config/` only). Any
  module-provided config default is invisible to console commands and
  migrations. Pre-existing, not yet fixed. **Relevant Marko package
  already partly adopted for a different purpose (see §14)**:
  `marko/config` — its own `ConfigDiscovery`/`ConfigServiceProvider`
  already implement exactly this "merge module config + root config"
  pattern; worth evaluating whether `bin/zoosper` should build its config
  the same way `ApplicationFactory` does, using the same merged source.
- **[R2] `bin/pest.sh` hides output on failure, and isn't even wired up** —
  `composer test` still resolves to `@php pest` (no `pest` file exists at
  root); `deploy`'s closing message tells users to run `composer test`
  anyway. Pre-existing, not yet fixed.
- **[R2] AI-session tooling scripts shouldn't be in the repo** —
  `collect-and-run.sh` (reads arbitrary filenames and shell commands from
  stdin, executes via `bash -lc`), `bin/cleanup-legacy-tooling.sh`,
  `bin/cleanup-old-root-tests.sh`. Pre-existing (predates this session's
  involvement), not yet removed.

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
- [~] Admin/module dependency decoupling (Phase 1.41): two-factor (full),
  media (full), page (partial — `AdminFormConfigAggregator` +
  `AdminConfigLayeredFileLoader` deliberately left in `zoosper-admin`).
  Independently confirmed complete for two-factor/media by a reviewer pass.
- [ ] **[R] Five simultaneously-active module-home conventions**
  (`app/*`, `packages/*`, `modules/*`, `modules/*/*`, Composer `vendor/*/*`)
  — reviewer calls this a footgun; `ModuleRegistry::enabledModules()`
  silently dedupes name collisions with zero error/log line. Not yet fixed.
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
- [ ] **[R] CSP will break real markup the moment report-only → enforce.**
  No `'unsafe-inline'`, but `admin/users/form.latte` reportedly has an
  inline `onclick` handler; also no `report-uri`/`report-to` configured at
  all, so violation data isn't even being collected. Not yet fixed.

## 4. Admin & Auth

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
- [ ] **[R] Two parallel 2FA crypto implementations reportedly exist**
  (`TwoFactor\Crypto\SecretProtector` — the live, confirmed-wired one — vs.
  `TwoFactor\Service\TwoFactorSecretProtector` + a whole parallel
  British-spelling `Enrolment` family). **Still not independently
  verified** — needs a direct `grep -r "TwoFactorSecretProtector\|
  AdminTwoFactorRepository" app/` before assuming real; if confirmed,
  delete whichever stack is genuinely dead.
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
  `derivatives:` argument to `MediaUploadService`. Not yet verified/fixed.
- [ ] **[R] Both media upload controllers reportedly construct their own
  private `MediaUploadService`** instead of the container-configured one
  (with cleanup/derivative dispatcher wired in). Not yet verified/fixed.

## 7. Mail

- [x] SMTP mailer + logged mailer + email log repository/admin viewer
- [x] Mail diagnostics + Mailpit local testing

## 8. API

- [x] API module (Auth, ContentPage, Health, Me)
- [ ] Headless API parity (roles, themes, url-rewrites CRUD)
- [ ] ContentPage API exposes structured Editor.js JSON (not serialized HTML)

## 9. Modular Asset Pipeline

- [x] Asset registry / resolver / controller (path-safe, MIME allowlist, ETag)
- [ ] Wire `/asset/{module}/{path}` route + `asset()` helper live
- [ ] Cache asset-registry scans per request
- [ ] **[R] Asset pipeline route is deliberately unauthenticated** — all
  security rests on `AssetResolver`'s path-traversal checks. Recommend
  fuzz-testing (encoded traversal, null bytes, symlinks) + a realpath
  containment check as a second layer.

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
- [ ] **[R2] AI-session tooling scripts should not be in the repo at
  all** — `collect-and-run.sh`, `bin/cleanup-legacy-tooling.sh`,
  `bin/cleanup-old-root-tests.sh`. Not yet removed.
- [ ] CI workflow (validate, Psalm, Pest+coverage, gate on every PR)
- [ ] Fix composer `gate` script to `@php` (not hardcoded `php8.5`)
- [ ] **[R2] `bin/pest.sh` hides output on failure and isn't wired up;
  `composer test` is still broken `@php pest`** — see §0.
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
6. Delete one of the two 2FA crypto/TOTP implementation families — **still
   not independently verified as real**, see §4
7. Finish admin-decoupling: relocate the last 2 classes so `zoosper-page`
   can drop `zoosper/admin`
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

## Daily log (most recent first)

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

