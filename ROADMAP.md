# Zoosper CMS — Master Roadmap

> **Single source of truth for high-level feature status.**
> High-level only — one line per capability, not per micro-phase. Detailed phase
> notes live under `docs/`. Update this file once a day at wrap-up: tick completed
> items, add newly planned ones, move things between sections.

**Last updated:** 2026-07-29 (Sydney)
**Framework baseline:** PHP 8.5 · Pest/PHPUnit · Psalm · Latte
_(Note: `marko/framework ^0.8` is listed in composer.json, but a 2026-07-29
reviewer pass found no reference to any Marko namespace anywhere in the
router/DI container/request-response/migrations/console subsystems — this
line needs verifying and correcting; see §13 item 14.)_

Legend: `[x]` done & deployed · `[~]` in progress / partial · `[ ]` planned
`[R]` reported by external reviewer, **not yet independently verified against actual code**

---

## 0. TOP PRIORITY — next phase

Two 2026-07-29 reviewer findings are serious enough to sit above everything
else once coding resumes. **Neither has been independently confirmed against
the live code yet** — verify first, then fix if confirmed:

- **[R] Possible privilege escalation via OR-permission routes.**
  `/admin/users/edit` is reportedly gated by `['role.manage', 'user.manage']`
  (either sufficient), but `UserAdminController::update()` allegedly accepts
  `role_ids` from the form and writes them unconditionally — meaning an
  admin with only `user.manage` could grant themselves `super_admin`.
  Symmetrically, `RoleAdminController::update()` (gated only by
  `role.manage`) allegedly lets a role-manager reassign user↔role
  membership with no `user.manage` check. **If confirmed, this is the
  single highest-priority fix on this entire roadmap** — gate role/permission
  assignment specifically by `role.manage`, independent of which permission
  got you into the controller.
- **[R] Rate-limit store has a real race condition.**
  `DatabaseRateLimitStore::recordAttempt()` reportedly does
  SELECT → branch → INSERT/UPDATE with no `SELECT ... FOR UPDATE` and no
  atomic upsert — two concurrent login attempts (i.e. exactly a brute-force
  script) could both miss the row, both attempt INSERT, and the second hits
  the unique constraint and throws an uncaught `PDOException` → 500 on a
  legitimate concurrent login. Fix pattern already exists correctly
  elsewhere in this codebase (`EntityExtensionValueRepository::upsert()`) —
  should be a small, well-contained fix once confirmed.

Also still true from before: rate limiting on `/admin/login` is wired but
**report-only only** — see §5, this was always the deliberately-deferred
state per the project's own ADR, not a regression.

---

## 1. Core Platform & Architecture

- [x] Modular package layout (`app/zoosper-*`) with per-module `composer.json` + PSR-4
- [x] Service container + module-owned `config/services.php` providers
- [x] Module route loader (admin + API) & controller providers
- [x] Config aggregation from module `config/*`
- [x] Declarative schema engine (validator, migrator, snapshot audit)
- [x] Driver-aware schema detection (MySQL + SQLite)
- [x] Entity save lifecycle + listener discovery
- [x] Entity extension (EAV) data persistence
- [x] Event/observer bus (module-discovered listeners)
- [x] Method plugin / interceptor system (report-only seam)
- [x] Core ⇄ feature decoupling (+ behavioural namespace-ban guard test —
  a 2026-07-29 reviewer independently called this "a legitimately well-built
  test," doing a real namespace-ban scan rather than a string grep)
- [x] Site-lookup boundary: core-owned `SiteLookupInterface` + `NullSiteLookup`
- [x] Frontend fallback handler wiring (fatal fixed) + boot-and-serve test
- [x] All 12 modules promoted to real Composer packages (Phase 1.40)
- [x] Module-owned migrations (Phase 1.40c)
- [x] Console/kernel decoupling — `admin:create`/`site:create`/`page:create`
  discovered per-module via `ModuleConsoleCommandLoader`
- [~] Admin/module dependency decoupling (Phase 1.41): two-factor (full),
  media (full), page (partial — 9/11 admin-form classes relocated to
  `Zoosper\Core\Form`; `AdminFormConfigAggregator` +
  `AdminConfigLayeredFileLoader` deliberately left in `zoosper-admin` for
  now). **A 2026-07-29 reviewer independently confirmed** the two-factor
  and media decoupling is genuinely complete ("good, real work"), and
  confirmed page's `composer.json` still hard-requires `zoosper/admin` as
  a direct result of those 2 unrelocated classes — matches our own
  understanding exactly. _(See §13 item 7 for the "finish this" follow-up.)_
- [ ] **[R] Five simultaneously-active module-home conventions**
  (`app/*`, `packages/*`, `modules/*`, `modules/*/*`, Composer `vendor/*/*`)
  — reviewer calls this "not flexibility, it's a footgun," and flags that
  `ModuleRegistry::enabledModules()` silently dedupes name collisions by
  keeping whichever is found first, with zero error/log line. Already
  listed as unresolved in this roadmap; reviewer adds the silent-collision
  detail as a new, sharper reason to prioritize it. _(§13 item 1)_
- [ ] **[R] No compiled/cached module discovery.** Reviewer counts ~15
  independently hand-rolled "glob config/X.php per enabled module, require,
  merge" loops (routes, services, events, menu, assets, translations, ACL,
  grid columns, method plugins...) with none cached beyond
  `ModuleRegistry`'s own module-list memoization — every request re-globs
  and re-requires across all ~15 mechanisms. Proposed fix: one discovery
  kernel emitting a single compiled `var/cache/module-map.php`, invalidated
  by an explicit `bin/zoosper module:compile`/`cache:clear` command.
  _(§13 item 2 — this is the same "compile step" idea both reviewer passes
  have now independently proposed)_
- [ ] **[R] No FK support in the declarative schema engine, no down-migrations.**
  `SchemaTable` only models columns + indexes, no constraint concept — every
  table built through it (`page_site_assignments`, `admin_user_two_factor`,
  `entity_extension_values`, `rate_limit_buckets`, `media_assets`,
  `url_rewrites`, etc.) has zero referential integrity. Low blast-radius
  today only because no admin screen supports deleting anything yet (see
  §13 item 10) — becomes a real orphaned-row problem the moment delete
  functionality ships. Schema engine is also explicitly additive-only by
  design (documented in `Schema/README.md`) — no supported path for column
  type/length changes, renames, or removal.
- [ ] Container autowiring
- [ ] Module lifecycle (install/enable/disable/uninstall)
- [ ] Composer packaging + 0.x tag + CHANGELOG + stability contract —
  reviewer adds: every internal module dependency currently uses
  `"zoosper/x": "*@dev"` (unconstrained wildcard) with no version-range
  checking in `ModuleDependencyValidator`, so nothing actually stops
  incompatible module versions being combined once this matters.

## 2. Sites, Pages & Content

- [x] Multi-site + site domains (store-view model) + admin CRUD
- [x] Pages CRUD (admin) + revisions
- [x] SEO metadata fields
- [x] Editor.js content model + JSON save pipeline
- [x] Block JSON → HTML rendering + HTML sanitization (HTMLPurifier)
- [x] Frontend page rendering via themes
- [~] `content_json` frontend rendering via `PageRenderer` (planned deepening)
- [ ] Router path parameters
- [ ] Consolidate `pages` table into declarative schema (remove file-migration split)
- [ ] **[R] No delete/archive on any admin CRUD screen** — reviewer flags
  this as "a basic missing feature, not just a modularity concern," and
  notes it's the reason the missing-FK gap above hasn't caused visible
  damage yet. Genuinely worth prioritizing over some architecture work.

## 3. Themes & Templating

- [x] Latte + PHP template engine adapters
- [x] Theme repository + per-site theme selection + theme admin
- [x] Module/theme template overrides (path-safe) + layout update system
- [~] RoleAdmin → Latte cutover (users on Latte; roles still PHP views)
- [ ] Adopter theme override story documented end-to-end
- [ ] **[R] CSP will break real markup the moment report-only → enforce.**
  `config/security.php` ships `script-src 'self'` with no `'unsafe-inline'`,
  but `admin/users/form.latte` reportedly has an inline `onclick="return
  confirm(...)"` handler that would be blocked outright. Also: **no
  `report-uri`/`report-to` is configured at all**, so violation data isn't
  even being collected during the report-only "tuning" period the roadmap
  already describes. Add reporting first, then either add a nonce mechanism
  or remove the inline handler, before ever flipping to enforce.

## 4. Admin & Auth

- [x] Admin authentication + session guard
- [x] Roles, permissions, ACL tree + admin users CRUD
- [x] CSRF + auth middleware pipeline (OR-permission semantics)
- [x] Audit log + login history
- [x] Admin navigation / dynamic menu
- [x] Admin form section + processor registries (extensible) — **[R]
  reviewer notes this newer system is only actually used by the Page form;
  every other admin form still uses an older, non-extensible
  `AdminFormDefinition`/`AdminFormField` pair, which itself looks orphaned.
  The "any module can extend any admin form" story is true for 1 form, not
  uniformly true. _(§13 item 8)_**
- [x] i18n / translations / admin locale preference
- [x] 2FA (TOTP) enrolment, reset, recovery-code generation
- [x] 2FA enforced at login (Phase 1.107), with recovery-code redemption
- [x] Login history recording bug fixed (Phase 1.113)
- [ ] **[R] Two parallel, un-reconciled 2FA crypto implementations
  reportedly exist**: `TwoFactor\Crypto\SecretProtector` (libsodium
  secretbox) vs. `TwoFactor\Service\TwoFactorSecretProtector` (OpenSSL
  AES-256-GCM, different key derivation), with a whole parallel
  Enrolment/British-spelling family (`AdminTwoFactorRepository`,
  `Service\Base32`, `Service\TotpVerifier`, `Service\RecoveryCodeGenerator`,
  `Service\TotpSecretGenerator`) allegedly dead code never deleted after a
  rename. **NOT YET VERIFIED** — the only `zoosper-two-factor/config/services.php`
  content reviewed together this session showed just one (American-spelling)
  stack registered. Needs a direct `grep -r "TwoFactorSecretProtector\|
  AdminTwoFactorRepository" app/` before assuming this is real; if
  confirmed, delete whichever stack is genuinely dead.
- [ ] **[R] `bin/zoosper key:generate` doesn't exist.** `config/two_factor.php`
  falls back through `TWO_FACTOR_ENCRYPTION_KEY` → `APP_KEY` → the literal
  string `'change-me-before-production'`. If `APP_KEY` is never set, TOTP
  secrets get encrypted with a well-known default. Should fail loudly
  (refuse to boot outside local env) rather than silently falling back.
- [ ] Memoize `SessionGuard::user()` per request — **[R] reviewer adds:**
  this and similar instance-level caches (`AuthService::$dummyHash`,
  `ModuleRegistry::$cachedModules`, `GridColumnRegistry::$cachedContributions`)
  are safe under classic PHP-FPM (fresh process per request) but become a
  genuine cross-request session-confusion risk if long-lived workers
  (Swoole/FrankenPHP) are ever adopted, since `ModuleRegistry::clearCache()`'s
  own docblock already anticipates that runtime model. Needs an explicit
  "rebuild request-scoped services per request" story before any such move,
  not just a manual `clearCache()` escape hatch on one class.
- [~] Admin god-module split — Page/User/Role admin controllers relocated;
  `ThemeAdminController` not yet moved.
- [x] Batch-load permissions in `AdminUserRepository` (fix N+1) — done, Phase 1.109
- [ ] Pagination + retention for audit log & login history
- [ ] **[R] Two competing, incomplete Grid extensibility systems.** The
  newer `GridDefinition`/`GridCriteria`/`GridColumnRegistry` system genuinely
  lets third-party modules contribute columns/filters to a grid they don't
  own — reviewer specifically credits this as working correctly for Audit
  Log and Login History (e.g. two-factor adding a `user_agent` column to
  Login History with zero changes to the owning module). But Pages, Sites,
  Site Domains, Roles, and Media all reportedly still use older, bespoke,
  non-extensible `*GridCriteria`/`*GridRepository` pairs. The "any module
  can extend any admin screen" story is true for 2 screens, not 6+.
  _(§13 item 8)_
- [ ] **[R] Grid "sortable" columns silently ignored by some repositories.**
  `GridCriteria::fromValues()` resolves `sortBy`/`sortDir` against the grid
  definition, but `AuditLogRepository::paginate()` and
  `LoginHistoryRepository::paginate()` reportedly hardcode `ORDER BY id
  <direction>` and never reference `$criteria->sortBy` at all — currently
  harmless only because both grids declare just one sortable column today.
  No test currently enforces that a `GridDataSourceInterface` implementation
  actually honors `sortBy`.

## 5. Security

- [x] Baseline security headers (X-Content-Type-Options, X-Frame-Options, Referrer/Permissions)
- [x] Session cookie `secure` defaults from request scheme (HTTPS-aware)
- [x] Content-Security-Policy (report-only) + HSTS (HTTPS-only) —
  **see §3 for the reviewer-flagged inline-handler + missing-report-uri gaps**
- [x] Constant-time authentication (no user-enumeration timing leak)
- [x] Rate-limiting subsystem built (store, guard, policy, report-only middleware)
- [x] Rate limiting wired onto `/admin/login`, report-only mode (2026-07-29) —
  deliberately non-enforcing per the project's own ADR. **[R] Reviewer
  independently confirmed this wiring is real** ("You've built a full stack
  ... and wired it onto /admin/login"), then flagged that the underlying
  `enforce` mode has literally no implementation anywhere — see §0.
- [ ] **[R] `PDO::ATTR_EMULATE_PREPARES` never explicitly disabled** in
  `ConnectionFactory::createMysqlConnection()`. On MariaDB this means
  client-side emulated binding rather than true server-side prepares —
  weaker type safety for bound values, thinner security margin. One-line,
  well-known fix: add `EMULATE_PREPARES => false`.
- [ ] **[R] Rate-limit identity salt defaults to empty string**
  (`config/rate_limit.php`: `'identity_salt' => ''`). With no salt, the
  SHA-256 `email|ip` hash is a straightforward dictionary/rainbow-table
  target — not the "opaque hash" the docblocks describe. Should require a
  randomly generated salt at install time, not default to empty.
- [ ] **[R] `HTML_SANITIZER_DRIVER=basic` (regex-based fallback) has no
  production guard.** The fallback sanitizer's own docblock admits it's
  bypassable; nothing refuses this driver outside local environments. If
  HTMLPurifier is ever missing from a prod install and someone flips the
  driver "to make it work," that's regex-based XSS protection on
  user-generated CMS content with zero warning.
- [ ] Enable report-only rate-limit mode in production config and begin
  collecting real data — precondition (per the ADR) before any future move
  to enforcement. **Note: per §0, "enforcement" currently has no code path
  to move to at all — this item now depends on §0's race-condition fix AND
  building an actual enforcing adapter, not just flipping a config flag.**
- [ ] CSP report-only → enforce (after tuning, and after adding
  report-uri + resolving the inline-handler conflict — see §3)
- [ ] Password min-length/complexity + `password_needs_rehash()` upgrade path
- [ ] Prod fail-closed when `SESSION_SECURE` unset
- [ ] CSRF decision for stateful `/api/*` session routes
- [x] Atomic admin writes (transaction-wrap user/role create+sync) — fixed
  2026-07-29 in both `RoleRepository` and `AdminUserRepository`
- [ ] **[R] `Request::form()` reads live `$_POST` directly**, unlike every
  other `Request` accessor (host, path, headers, query, siteContext,
  routeParams), which are pure/immutable/constructor-injected. Breaks the
  immutability contract: a manually-constructed `Request` (sub-requests,
  tests, future queue workers) won't see its intended form data reflected;
  tests have to mutate `$_POST` globally instead of constructing the
  object. Fix: capture the parsed body once in `fromGlobals()`, have
  `form()` read from that captured value.
- [ ] Structural email-log body redaction (code-enforced)
- [ ] `entity_extension_values` write-time field validation at repo boundary
- [ ] Truncate `user_agent` in audit/login-history
- [ ] **[R] Env parsing / `env()` helper concerns** (from a separate
  2026-07-29 reviewer pass on bootstrap internals) — reported operator-
  precedence bug in a global `env()` (`??` binds tighter than `?:`, so a
  falsy-but-set value like `DEBUG=0` silently collapses to the default),
  plus reported `.env` parser issues (inline comments not stripped, quote
  characters trimmed from values including a literal `"0"`, values never
  passed to `putenv()`) and **3 competing env implementations with no
  `function_exists()` guard** on the global `env()` function. **NOT YET
  VERIFIED against the live file** — needs a direct read of
  `bootstrap/autoload.php` before fixing. Also reported: a dozen
  `config/*.php` files each redefine an identical local `$env` closure
  inline instead of sharing one helper, and `config/html_sanitizer.php`
  reportedly uses a subtly different implementation that treats an
  empty-string `.env` value differently than the others do.

## 6. Media

- [x] Media library + admin upload
- [x] Editor.js image integration + upload endpoint
- [x] Local media derivative foundation (copy/no-op processor seam)
- [x] Media standalone package split — `zoosper-media`'s `composer.json` no
  longer requires `zoosper/admin` (Phase 1.41) — **[R] independently
  confirmed by the 2026-07-29 reviewer as genuinely complete**
- [x] Fixed: `MediaAdminController::upload()` silently swallowed all upload
  failures (2026-07-29)
- [ ] Media derivative processing continuation (resize/transform profiles)
  — **[R] reviewer reports the derivative dispatcher/policy/processor
  classes for this are fully built and tested via bespoke smoke scripts,
  but `packages/zoosper-media/config/services.php` never actually passes a
  `derivatives:` argument to `MediaUploadService` — 100% dead in
  production if accurate. Worth confirming before treating this as
  "in progress."**
- [ ] **[R] Both `MediaAdminController` and `MediaEditorJsUploadController`
  reportedly construct their own private `MediaUploadService` via
  constructor fallback instead of using the one the container is actually
  configured to build** (with cleanup service / derivative dispatcher
  wired in) — the canonical `config/services.php` registration would be
  orphaned if so. Worth confirming directly against the current
  `config/controllers.php` for media.
- [ ] **[R] `MediaUploadServiceResult::$stored` typed `?object` instead of
  the concrete `StoredMediaFile`** — combined with
  `(string) $result->stored?->publicPath` in the Editor.js controller, a
  null `$stored` would silently degrade to an empty string in a
  "successful" JSON response instead of surfacing as an error.

## 7. Mail

- [x] SMTP mailer + logged mailer + email log repository/admin viewer
- [x] Mail diagnostics + Mailpit local testing

## 8. API

- [x] API module (Auth, ContentPage, Health, Me)
- [ ] Headless API parity (roles, themes, url-rewrites CRUD)
- [ ] ContentPage API exposes structured Editor.js JSON (not serialized HTML)

## 9. Modular Asset Pipeline

- [x] Asset registry / resolver / controller (path-safe, MIME allowlist, ETag)
- [x] `config/assets.php` auto-discovery per module
- [ ] Wire `/asset/{module}/{path}` route + `asset()` helper live
- [ ] Cache asset-registry scans per request (avoid 4× re-scan)
- [ ] **[R] Asset pipeline route is deliberately unauthenticated, with all
  security resting on `AssetResolver`'s path-traversal/extension checks**
  (per that class's own comment). Reviewer recommends fuzz-testing it
  (encoded traversal, null bytes, double encoding, symlinks) and adding a
  second layer (realpath containment check) before wider exposure.

## 10. Caching & Performance

- [~] HTTP caching subsystem built (`HttpCachePolicy`, `CacheContext`, `CacheKeyBuilder`, fragments)
- [ ] Wire caching into responses OR remove it — currently inert
- [ ] Cache merged translation catalogue per locale
- [ ] `RoleAdmin::permissionTree()` via `ConfigRepository` (not runtime `require`)
- [ ] Rate-limit report sink rotation/retention (or DB store)
- [ ] **[R] Unbounded `?page=` in `Pager::fromQuery()`** — `page_size` is
  correctly clamped but `page` is not; a large page number produces a large
  `OFFSET` that MariaDB still has to scan-and-discard, a deep-pagination
  cost amplifier on the already-unbounded log tables above.
- [ ] **[R] No `COLLATE` explicitly pinned in any generated `CREATE TABLE`**
  — `SchemaSqlBuilder::createTableSql()` relies on the server's own
  `utf8mb4` default collation, which has changed across MariaDB major
  versions; the same schema on dev/staging/prod running different MariaDB
  point releases could silently produce different collations and subtly
  different sort/comparison/uniqueness behavior. Pin explicitly (e.g.
  `utf8mb4_unicode_ci`).

## 11. Quality, Tooling & Repo Hygiene

- [x] Pest + PHPUnit harness
- [x] Quality gate runner
- [x] Durable-tool manifest — **[R] reviewer's sharpest hygiene critique:
  `config/durable-tools.php` reportedly exists purely to stop cleanup
  automation from deleting one-off scripts that a Pest test happens to
  assert exist — "the tests are dictating what production config has to
  protect, that's inverted."** Worth sitting with this framing even though
  it's not a quick fix.
- [x] Boot-and-serve feature test
- [x] Duplicate PageMomentum stack + dead roles views removed
- [x] Tools prune (self-computing, reference-verified) — **[R] reviewer
  still counts ~150+ single-purpose audit-*/apply-*/verify-*/plan-*/
  discover-*/smoke-*/prove-*/guard-*/inspect-* scripts in `tools/`,
  several of which reportedly exist solely to plan or audit the eventual
  deletion of other scripts in the same directory. Recommends pruning to
  genuinely operational scripts only.**
- [x] Docs retained (link-aware pruner built but not applied)
- [ ] CI workflow (validate, Psalm, Pest+coverage, gate on every PR)
- [ ] Fix composer `gate` script to `@php` (not hardcoded `php8.5`)
- [ ] Query-count / N+1 regression tests (statement-execute counter)
- [ ] Response cache-header tests + module-scan-count-per-request test
- [x] Test: 2nd enrolled login cannot authenticate without OTP
- [ ] Docs website (from the retained `docs/` corpus)
- [ ] **[R] Test-suite signal-to-noise ratio.** Reviewer reports an entire
  `LegacyVerify*Test` family whose assertions are `file_get_contents()` +
  `str_contains()` checks against tooling scripts and status docs, not
  application behavior — plus a 15+ file `PageMomentum*`/
  `PageAdminDashboard*` test cluster built around a static internal
  "readiness" status page with reportedly zero end-user CMS value.
  Reviewer's proposed fix: replace string-grep "tests" with real behavioral
  HTTP round-trip tests (page CRUD, media upload, login+2FA, multi-site
  resolution). Explicitly credits real, good existing tests too
  (`TwoFactorChallengeServiceTest`, `AuthServiceRehashTest`,
  `RateLimitPolicySeamTest`, `MediaUploadRepositoryFailureCleanupTest`) —
  the claim is about ratio, not total absence of good tests.
- [ ] **[R] No public/internal API boundary between feature modules.**
  `CoreDecouplingArchitectureTest` (independently praised as "a legitimately
  well-built test") only enforces the Core→feature-module boundary; nothing
  enforces boundaries *between* feature modules. Proposed: extend the same
  namespace-scan approach pairwise across modules.

## 12. Page Momentum (visible admin dashboard)

- [x] Routed `/admin/page-momentum` with real read-only facts
- [ ] Depth: more cards + 7-day trend deltas (deferred)
- [ ] **[R] Reviewer recommends deleting or radically shrinking this
  feature** — reports 15+ test files and a dozen provider/presenter/shell
  classes built to render a static "readiness" status page, calling it a
  disproportionate amount of engineering surface area relative to actually
  missing features like page delete. Worth a genuine judgment call, not
  just accepting the recommendation outright — this feature was built
  deliberately for visible progress tracking per earlier product decisions.

## 13. Consolidated Roadmap-to-"true-modular" (from 2026-07-29 reviewer pass)

Numbered to match the reviewer's own priority ordering. Cross-referenced to
sections above rather than duplicated in full.

1. Pick one canonical module-home convention (see §1)
2. Single compiled module-manifest discovery kernel (see §1)
3. Real ALTER/removal support + FK declarations in the schema engine (see §1, §2)
4. Real rate-limit enforcement + account lockout + password reset + fail-closed
   crypto defaults + `EMULATE_PREPARES => false` + pinned collation +
   environment-guarded sanitizer driver (see §0, §4, §5, §10)
5. Fix the `role.manage`/`user.manage` privilege boundary (see §0 — TOP PRIORITY)
6. Delete one of the two 2FA crypto/TOTP implementation families, once confirmed real (see §4)
7. Finish admin-decoupling: relocate the last 2 classes so `zoosper-page` can
   genuinely drop `zoosper/admin`; audit every other module for the same
   residual pattern (see §1)
8. Consolidate the two Grid systems and two AdminForm systems into one;
   retrofit Pages/Sites/Domains/Roles/Media onto it (see §4)
9. Standardize module naming to one convention; real semver constraints
   instead of `*@dev`; version-range checking in `ModuleDependencyValidator` (see §1)
10. Add delete/archive to every admin CRUD screen (see §2)
11. Enforce a public/internal API boundary between every pair of feature
    modules, extending `CoreDecouplingArchitectureTest`'s approach (see §11)
12. CI pipeline gated on Pest, static analysis, and architecture-boundary tests (see §11)
13. Purge `tools/` to operational scripts only; resolve the Page Momentum
    question one way or the other; stop committing single-use agent-phase
    artifacts as permanent product code going forward (see §11, §12)
14. Reconcile or remove the `marko/framework` roadmap claim — no reviewed
    subsystem actually references any Marko namespace

---

## Daily log (most recent first)

- **2026-07-29 (reviews)** — Received and read two further review passes
  (Fable's bootstrap/composer/repo-hygiene review; Sonnet's full-module
  security/consolidation review). Logged all findings above, tagged `[R]`
  where not yet independently verified against the actual current code.
  Two findings flagged as top priority for next session: a possible
  privilege-escalation gap in role/permission assignment, and a real race
  condition in the rate-limit store. Declined to treat every claim as
  automatically true — noted one reviewer claim (that `composer test`
  scripts are broken) contradicts this session's own repeated, successful
  `zcomposer test` runs, and flagged the "two competing 2FA crypto stacks"
  claim as needing direct verification since it wasn't visible in the
  `config/services.php` content reviewed together earlier. No code changes
  made today as a result of these reviews — verification and fixes deferred
  to next session.
- **2026-07-29 (build)** — Wired the existing, fully-built report-only
  rate-limit engine onto `/admin/login` for the first time. Fixed
  `MediaAdminController::upload()` silently swallowing failures. Fixed
  non-atomic writes in both `RoleRepository` and `AdminUserRepository`
  (corrected an earlier inaccurate claim that `AdminUserRepository` was
  already fixed).
- **2026-07-29 (earlier)** — Corrected roadmap/code drift on login-time 2FA
  enforcement (already implemented, Phase 1.107). Completed Phase 1.41
  (admin/module decoupling) for two-factor and media (full), page
  (partial). Completed Phase 1.40 (Composer packages), Phase 1.40c
  (module-owned migrations), console/kernel decoupling.
- **2026-07-25** — Frontend fatal fixed + boot test; PageRepository SQLite
  fix; site-lookup alignment; CSRF fix; security batch (secure session
  default, CSP+HSTS report-only, constant-time auth); tools prune.

> Add a new dated bullet each day at wrap-up. Keep bullets to one or two lines;
> the checklists above are the durable state.
