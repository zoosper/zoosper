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

**Last updated:** 2026-07-30 (Sydney)
**Framework baseline:** PHP 8.5 · Pest/PHPUnit · Psalm · Latte · **Marko packages
(real, adopted usage — see §14) via `zoosper/errors`**

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

**New top priority, from a live production incident (2026-07-30):**

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

**New top priority, from a second Fable review pass (2026-07-30), one item
already fixed today:**

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
- **[R2] Every CLI command requires a live database**, even `help`,
  `compile`, `cache:clear` (a *recovery* tool, unavailable exactly when
  most needed) — confirmed present in `bin/zoosper`'s structure since
  before this session's involvement; not yet fixed. Fix: connect to the DB
  lazily, only for commands that actually need it.
- **[R2] CLI and HTTP read different configuration** — web boot aggregates
  config via `ModuleConfigAggregator` (module configs + root); `bin/zoosper`
  uses `ConfigRepository::fromPath()` (root `config/` only). Any
  module-provided config default is invisible to console commands and
  migrations. Pre-existing, not yet fixed.
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

## 14. Marko Framework Adoption Strategy (new section, 2026-07-30)

The project's original architecture intent (see `README.md`: "...inspired
by ... Marko PHP module conventions") was to build as much of Zoosper as
possible on top of real Marko packages rather than reinventing them. That
intent was not consistently followed prior to this session — `marko/core`,
`marko/errors`, `marko/errors-simple` were installed in `vendor/` but
completely unused until 2026-07-30. Going forward, the discipline is:
**before writing any new subsystem from scratch, check Marko's real package
catalog first** (by reading actual installed source / real docs — not
guessing from package names).

### Adopted (real, verified integration)
- **`marko/core` (`MarkoException`)** — `ZoosperException` now formally
  `extends MarkoException` (additive: `ZoosperException` was already a
  strict superset — `docsUrl`/`details` have no Marko equivalent). Verified
  via the actual installed source, not documentation alone.
- **`marko/errors` (`ErrorReport`, `Severity`)** — real error-reporting
  pipeline; `ZoosperException` is automatically recognised by
  `ErrorReport::fromThrowable()`'s own `instanceof MarkoException` check,
  with zero glue code.
- **`marko/errors-simple` (`TextFormatter`, `BasicHtmlFormatter`,
  `CodeSnippetExtractor`, `Environment`)** — real CLI/web exception display,
  wired via a new `Zoosper\Errors\ExceptionDisplayer` class (see below).
  Deliberately NOT a wholesale replacement of `ErrorHandler` with Marko's
  own `SimpleErrorHandler` — that class has no file-based logging at all;
  composing (log via existing `LocalLogger`, then display via Marko's
  formatters) preserves existing log output while adding real display.

### New package extracted: `zoosper/errors`
First module extracted out of `zoosper-core` into its own standalone
package (`packages/zoosper-errors`), following the same path-repository
pattern already proven by `packages/zoosper-media`. Owns
`ZoosperException`, `SensitiveValueRedactor`, `ConsoleExceptionFormatter`,
and the new `ExceptionDisplayer` (which owns every direct `Marko\*` import
— `zoosper-core` itself now has **zero** direct Marko dependency in its own
`composer.json`, only `zoosper/errors`, which transitively provides
`marko/errors`+`marko/errors-simple`). This is a clean architectural
template: exception/error handling is a near-leaf dependency (depends on
almost nothing else, but almost everything depends on it), making it the
right first extraction candidate. **Next extraction candidate from
zoosper-core is not yet chosen** — to be decided once the database/cache
work below clarifies natural boundaries.

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

### Newly relevant given the high-traffic + read-replica assumption (2026-07-30)
- **`marko/database-readwrite`** — a small, genuinely additive decorator:
  routes writes to one primary connection and reads to one or more
  replicas (random or weighted), wrapping *any* existing driver connection
  rather than requiring the full `marko/database` entity-mapper rewrite.
  **This is the concrete next step for read-replica support** — does not
  require adopting `marko/database` itself. Next action: read the actual
  installed source/API directly (same discipline as every other adoption
  this session) before designing the integration.

### Not yet researched — queued for the next planning pass
- **`marko/cache`** — relevant to §10's "wire the inert HTTP cache
  subsystem, or remove it" decision, sharpened by the high-traffic
  assumption.
- **`marko/config`** — relevant to the CLI-vs-HTTP config divergence bug
  (§0 [R2]) and the "~15 uncached per-request loops" performance item (§1).
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

## 15. High-Traffic & Read-Replica Readiness (new section, 2026-07-30)

Planning assumption, stated explicitly by the project owner: this CMS may
need to support a high-traffic website and may require a database read
replica. This reframes several existing roadmap items from "someday" to
"soon":

- **Read replica support**: adopt `marko/database-readwrite` as a wrapping
  decorator around the existing `ConnectionFactory` output — additive, not
  a rewrite (see §14). Not yet started; needs the real API read first.
- **Finish or remove the inert HTTP cache subsystem** (§10) — a
  built-but-unwired cache is wasted effort at real traffic. Needs a
  decision: wire it now, or explicitly remove it.
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
  fail-back to live scan if missing/corrupt. First of ~15 per-request
  discovery loops to be cached (see §15 for continuing this).
- [x] **[FIXED] Migrations always use live module discovery** — see §0,
  the stale-cache deploy bug.
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

- [~] HTTP caching subsystem built but inert — **see §15: sharpened from
  "someday" to "soon" by the high-traffic assumption**
- [ ] Wire caching into responses OR remove it
- [ ] Cache merged translation catalogue per locale
- [x] **[FIXED] Unbounded `?page=` in `Pager::fromQuery()`** —
  `page_size` was clamped but `page` was not, allowing an arbitrarily huge
  `OFFSET`. Fixed with a fixed, generous safety ceiling (default 100,000),
  mirroring the existing `page_size` cap.
- [x] **[FIXED] No `COLLATE` pinned in generated `CREATE TABLE`** —
  `SchemaSqlBuilder` now explicitly pins `utf8mb4_unicode_ci` on the MySQL
  branch, preventing silently different collation behavior across
  environments running different MariaDB point releases. Only affects
  newly-created tables going forward, not existing live data.
- [ ] Rate-limit report sink rotation/retention (or DB store)

## 11. Quality, Tooling & Repo Hygiene

- [x] Pest + PHPUnit harness; quality gate runner
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
  exist alongside them (named examples in the previous roadmap version) —
  this is about ratio, not total absence.
- [ ] **[R] No public/internal API boundary between feature modules** —
  `CoreDecouplingArchitectureTest` only enforces Core→feature; nothing
  enforces boundaries between feature modules.
- [x] **New this session**: comment-verbosity self-correction needed. A
  reviewer pass fairly noted that detailed "FIX (confirmed 2026-07-30...)"
  narrative essays now live inside source files, and will go stale after
  the next refactor — that history belongs in commit messages/CHANGELOG,
  not permanently embedded in code comments. **Decision needed**: agree on
  a lighter-weight in-code documentation convention going forward (see
  "Open questions" below).

## 12. Page Momentum (visible admin dashboard)

- [x] Routed `/admin/page-momentum` with real read-only facts
- [ ] **[R] Reviewer recommends deleting or radically shrinking this** —
  15+ test files/dozen classes for a static readiness page, vs. missing
  features like page delete. Explicit judgment call, not yet decided.

## 13. Consolidated "true-modular" roadmap (from reviewer passes)

1. Pick one canonical module-home convention (§1)
2. Extend the compile step beyond just the module list (§1, §15)
3. Real ALTER/removal support + FK declarations in the schema engine (§1, §2)
4. **[Substantially advanced this session]** Real security hardening:
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
14. **[Done]** Reconciled the `marko/framework` roadmap claim — see §14;
    real, verified Marko adoption now underway (`zoosper/errors`), with a
    clear strategy for what's next vs. deliberately deferred

---

## Open questions for the next planning session

1. **In-code comment verbosity**: shift from detailed narrative "FIX
   (confirmed date, reviewer pass)" essays in source comments toward
   shorter, timeless doc-comments — with the full "why/when/who found it"
   story living only in commit messages and this roadmap? (Raised by a
   reviewer pass, and fair.)
2. **Build order confirmation** (see today's planning discussion): research
   `marko/cache`/`config`/`cli` → design `marko/database-readwrite`
   adoption → decide on the HTTP cache subsystem → pick the next
   `zoosper-core` extraction candidate → remaining Fable pass #2 findings
   (lazy CLI DB connection, CLI/HTTP config unification, `pest.sh`
   wiring, removing AI-session tooling scripts).
3. Rate-limit enforcement timeline, given the high-traffic assumption
   sharpening urgency vs. the ADR's "collect real report-only data first"
   precondition.

---

## Daily log (most recent first)

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
  Marko framework integration (`MarkoException`, `ErrorReport`,
  `TextFormatter`/`BasicHtmlFormatter` via a new `ExceptionDisplayer`
  boundary class) — confirmed `zoosper-core` itself now has zero direct
  Marko dependency. Received and read a second Fable review pass; logged
  new findings, most still open. Confirmed `marko/database`/
  `database-mysql` are too large a rewrite to adopt now, but
  `marko/database-readwrite` is genuinely relevant given a stated
  high-traffic + read-replica planning assumption — queued as next
  research target alongside `marko/cache`/`config`/`cli`.
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
