# Zoosper CMS — Master Roadmap

> **Single source of truth for high-level feature status.**
> High-level only — one line per capability, not per micro-phase. Detailed phase
> notes live under `docs/`. Update this file once a day at wrap-up: tick completed
> items, add newly planned ones, move things between sections.

**Last updated:** 2026-07-29 (Sydney)
**Framework baseline:** PHP 8.5 · `marko/framework ^0.8` · Pest/PHPUnit · Psalm · Latte

Legend: `[x]` done & deployed · `[~]` in progress / partial · `[ ]` planned

---

## 0. TOP PRIORITY — next phase

- No outstanding CRITICAL security item at this time. The previous top item
  (login-time 2FA enforcement) was found, on code inspection, to already be
  implemented — see §4 and the 2026-07-29 daily log entry below for detail on
  how this drift between roadmap and code happened.
- Highest-value remaining candidates (not yet started, pick one next):
  - CI workflow (validate, Psalm, Pest+coverage, gate on every PR) — cheapest
    high-impact item outstanding; currently quality gates only run when
    remembered locally. _(§11)_
  - Wire rate-limiting live on `/admin/login` — subsystem is fully built
    (store, guard, policy, report-only middleware) but not connected. _(§5)_
  - Wire or remove the inert HTTP caching subsystem — built but pages ship no
    cache headers. _(§10)_
  - Admin god-module split (Page/User/Role/Theme admin screens → their owning
    feature modules) — see §1 for what's already been done here.

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
- [x] Core ⇄ feature decoupling (+ behavioural namespace-ban guard test)
- [x] Site-lookup boundary: core-owned `SiteLookupInterface` + `NullSiteLookup`
- [x] Frontend fallback handler wiring (fatal fixed) + boot-and-serve test
- [x] **All 12 modules promoted to real Composer packages** (Phase 1.40) —
  `api`/`mail`/`two-factor`/`url-rewrite`/`install` were previously loaded
  only via root autoload map, bypassing Composer's dependency resolution
  entirely ("phantom packages"). Root `autoload.psr-4` module duplication
  removed; each module now owns its own autoload. Sync/verify tooling
  (`tools/sync-module-autoload.php`, `tools/verify-module-autoload-sync.php`)
  deleted as no longer needed.
- [x] **Module-owned migrations** (Phase 1.40c) — 8 migration files relocated
  from a single root `database/migrations/` folder into
  `zoosper-auth`/`zoosper-site`/`zoosper-page`/`zoosper-admin`'s own
  `database/migrations/` folders; `Migrator` now discovers migrations
  per-module via `ModuleRegistry`, same pattern already used for declarative
  schema. One dead, never-executed `.sql` file removed.
- [x] **Console/kernel decoupling** — `admin:create`, `site:create`,
  `page:create` moved out of hardcoded `bin/zoosper` blocks into their owning
  modules (`zoosper-auth`, `zoosper-site`, `zoosper-page`) via the existing
  `ModuleConsoleCommandLoader` + `config/console.php`. The kernel now only
  retains `migrate` and the `make:*` scaffolding commands, which are
  legitimately kernel-level. New shared `Zoosper\Core\Console\ConsoleOptions`
  helper for `--key=value` parsing.
- [~] **Admin/module dependency decoupling (Phase 1.41)** — the same
  interface-in-Core(-or-Auth)/implementation-in-Admin pattern already proven
  for `SiteLookupInterface` applied to three modules' dependency on
  `zoosper-admin`:
  - [x] `zoosper-two-factor` — fully decoupled; `composer.json` no longer
    requires `zoosper/admin`. New `AuditLoggerInterface`,
    `LoginHistoryRecorderInterface` (Core), `AdminLayoutRendererInterface`
    (Auth — needs `AdminUser`, and every consumer already requires
    `zoosper/auth`). Along the way, fixed a silent bug where
    `AdminTwoFactorResetService`'s audit-log call had mismatched argument
    types under `strict_types=1`, throwing every time and silently
    swallowed — 2FA resets were never actually being audit-logged.
  - [x] `zoosper-media` — fully decoupled; `composer.json` no longer
    requires `zoosper/admin`. New `AdminViewRendererInterface` (Auth). Also
    removed a dead, unused `AdminLayout` constructor parameter from
    `MediaAdminController` entirely.
  - [~] `zoosper-page` — partially decoupled. `PageAdminController` now uses
    `AdminLayoutRendererInterface`/`AdminViewRendererInterface`, and 9 of 11
    admin-form classes (`AdminFormSection`, `AdminFormSectionProviderInterface`,
    `AdminFormProviderRegistry`, `AdminFormRenderer`,
    `AdminFormProcessorInterface`, `AdminFormProcessingResult`,
    `AdminFormProcessorRegistry`, `AdminFormConfigProviderFactory`,
    `AdminFormProcessorConfigFactory`) were relocated to `Zoosper\Core\Form`.
    **Deliberately NOT relocated:** `AdminFormConfigAggregator` and
    `AdminConfigLayeredFileLoader` remain in `zoosper-admin` — see
    `docs/development/phase-1-41-admin-module-decoupling.md` for the full
    reasoning (in short: these two classes are genuinely admin-runtime
    config-loading machinery, and are protected by a mature suite of 3 tests
    + 4 tools scripts + 2 docs files built during Phase 1.40's config-layering
    closure; the cost of unwinding that safety net exceeded the remaining
    architectural benefit). `zoosper-page`'s `composer.json` therefore still
    requires `zoosper/admin`.
- [ ] Request/process memoization for `ModuleRegistry::enabledModules()` _(§2.2/§3)_
- [ ] Single module-home convention (`app/` vs `packages/` vs `modules/`)
- [ ] Container autowiring
- [ ] Module lifecycle (install/enable/disable/uninstall) — migrations are now
  module-owned (see above), but enable/disable/uninstall hooks and
  per-module version tracking are not yet built
- [ ] Composer packaging + 0.x tag + CHANGELOG + stability contract

## 2. Sites, Pages & Content

- [x] Multi-site + site domains (store-view model) + admin CRUD
- [x] Pages CRUD (admin) + revisions
- [x] SEO metadata fields
- [x] Editor.js content model + JSON save pipeline
- [x] Block JSON → HTML rendering + HTML sanitization (HTMLPurifier)
- [x] Frontend page rendering via themes
- [~] `content_json` frontend rendering via `PageRenderer` (planned deepening)
- [ ] Router path parameters
- [ ] Consolidate `pages` table into declarative schema (remove file-migration split) _(§6)_

## 3. Themes & Templating

- [x] Latte + PHP template engine adapters
- [x] Theme repository + per-site theme selection + theme admin
- [x] Module/theme template overrides (path-safe) + layout update system
- [~] RoleAdmin → Latte cutover (users on Latte; roles still PHP views)
- [ ] Adopter theme override story documented end-to-end

## 4. Admin & Auth

- [x] Admin authentication + session guard
- [x] Roles, permissions, ACL tree + admin users CRUD
- [x] CSRF + auth middleware pipeline (OR-permission semantics)
- [x] Audit log + login history
- [x] Admin navigation / dynamic menu
- [x] Admin form section + processor registries (extensible)
- [x] i18n / translations / admin locale preference
- [x] 2FA (TOTP) enrolment, reset, recovery-code generation
- [x] **2FA enforced at login** (Phase 1.107) — a correct password for a
  2FA-enrolled user now enters a pending-2FA session state (`SessionGuard`
  tracks this separately from full authentication) and redirects to
  `/admin/2fa/challenge`; the session is only promoted to authenticated
  after a valid TOTP or recovery code. **Correction to a prior roadmap
  entry**: this item was previously listed above as the #1 critical
  outstanding gap — on 2026-07-29 code inspection confirmed it was already
  implemented; the roadmap text itself had simply gone stale relative to
  the code. See daily log below.
- [x] **Recovery-code redemption at login** — `TwoFactorChallengeService::verifyRecoveryCode()`
  is wired into `AdminTwoFactorChallengeController`, alongside TOTP
  verification.
- [x] **Login history recording bug fixed** (Phase 1.113) — the previous
  implementation probed `LoginHistoryRepository` for method names like
  `recordSuccess`/`success` via `method_exists()`, but the real method was
  always called `record()`. None of the probed names ever matched, so login
  history was silently never written. Fixed by calling `record()` directly
  with client IP + user agent (previously omitted despite the repository
  always accepting them).
- [ ] Memoize `SessionGuard::user()` per request _(§3.1)_
- [~] Admin god-module split (Page/User/Role/Theme → feature modules) —
  `PageAdminController`, `RoleAdminController`, `UserAdminController` already
  relocated to their owning modules (Phase 1.33d / Phase F1); `ThemeAdminController`
  not yet moved. Separately, Phase 1.41 (see §1) removed `zoosper-page`'s
  and `zoosper-two-factor`'s and `zoosper-media`'s dependency on
  `zoosper-admin`'s concrete UI/audit classes — a related but distinct kind
  of decoupling from the controller-relocation work.
- [ ] Batch-load permissions in `AdminUserRepository` (fix N+1) _(§4.1)_
- [ ] Pagination + retention for audit log & login history _(§4.2)_

## 5. Security

- [x] Baseline security headers (X-Content-Type-Options, X-Frame-Options, Referrer/Permissions)
- [x] Session cookie `secure` defaults from request scheme (HTTPS-aware)
- [x] Content-Security-Policy (report-only) + HSTS (HTTPS-only)
- [x] Constant-time authentication (no user-enumeration timing leak)
- [x] Rate-limiting subsystem built (store, guard, policy, report-only middleware)
- [ ] Rate limiting wired live on `/admin/login`
- [ ] CSP report-only → enforce (after tuning) _(§5)_
- [ ] Password min-length/complexity + `password_needs_rehash()` upgrade path _(§5)_
- [ ] Prod fail-closed when `SESSION_SECURE` unset _(§5)_
- [ ] CSRF decision for stateful `/api/*` session routes _(§5)_
- [ ] Atomic admin writes (transaction-wrap user/role create+sync)
- [ ] Structural email-log body redaction (code-enforced)
- [ ] `entity_extension_values` write-time field validation at repo boundary _(§6)_
- [ ] Truncate `user_agent` in audit/login-history _(§6)_

## 6. Media

- [x] Media library + admin upload
- [x] Editor.js image integration + upload endpoint
- [x] Local media derivative foundation (copy/no-op processor seam)
- [x] Media standalone package split — `zoosper-media`'s `composer.json` no
  longer requires `zoosper/admin` (Phase 1.41, see §1)
- [ ] Media derivative processing continuation (resize/transform profiles)

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
- [ ] Cache asset-registry scans per request (avoid 4× re-scan) _(§2.2)_

## 10. Caching & Performance

- [~] HTTP caching subsystem built (`HttpCachePolicy`, `CacheContext`, `CacheKeyBuilder`, fragments)
- [ ] **Wire caching into responses OR remove it** — currently inert; pages ship no cache headers _(§2.1)_
- [ ] Cache merged translation catalogue per locale _(§3.2)_
- [ ] `RoleAdmin::permissionTree()` via `ConfigRepository` (not runtime `require`) _(§3.3)_
- [ ] Rate-limit report sink rotation/retention (or DB store) _(§4.3)_

## 11. Quality, Tooling & Repo Hygiene

- [x] Pest + PHPUnit harness
- [x] Quality gate runner (site-lookup audit + tools hygiene + durable-registry integrity)
- [x] Durable-tool manifest consolidated to single `config/durable-tools.php`
- [x] Boot-and-serve feature test
- [x] Duplicate PageMomentum stack + dead roles views removed
- [x] Tools prune (self-computing, reference-verified)
- [x] Docs retained (link-aware pruner built but not applied) — consolidate later
- [ ] CI workflow (validate, Psalm, Pest+coverage, gate on every PR)
- [ ] Fix composer `gate` script to `@php` (not hardcoded `php8.5`)
- [ ] Query-count / N+1 regression tests (statement-execute counter) _(§7)_
- [ ] Response cache-header tests + module-scan-count-per-request test _(§7)_
- [x] Test: 2nd enrolled login cannot authenticate without OTP — covered by
  the existing 2FA challenge test suite (see §4; item was previously listed
  as planned, confirmed already covered on 2026-07-29 review)
- [ ] Docs website (from the retained `docs/` corpus)

## 12. Page Momentum (visible admin dashboard)

- [x] Routed `/admin/page-momentum` with real read-only facts
- [ ] Depth: more cards + 7-day trend deltas (deferred)

---

## Daily log (most recent first)

- **2026-07-29** — Corrected a significant roadmap/code drift: discovered
  during unrelated architecture work that login-time 2FA enforcement (listed
  above as the #1 CRITICAL outstanding item since 2026-07-25) was in fact
  already implemented in Phase 1.107, along with recovery-code redemption
  and a login-history recording bugfix (Phase 1.113). Updated §0 and §4 to
  reflect actual code state. Separately, completed Phase 1.41
  (admin/module dependency decoupling) for two-factor (full) and media
  (full), and partially for page (9/11 admin-form classes relocated to
  `Zoosper\Core\Form`; the remaining 2 classes deliberately kept in
  `zoosper-admin` — documented in
  `docs/development/phase-1-41-admin-module-decoupling.md`). Also completed
  Phase 1.40 (all 12 modules real Composer packages), Phase 1.40c
  (module-owned migrations), and console/kernel decoupling
  (`admin:create`/`site:create`/`page:create` moved to their owning
  modules).
- **2026-07-25** — Frontend fatal fixed + boot test; PageRepository SQLite fix;
  site-lookup read/write alignment; CSRF roles field fixed; duplicate/dead-code
  cleanup; durable-manifest consolidation; core-decoupling test; security batch
  (secure session default, CSP+HSTS report-only, constant-time auth); tools prune;
  decided to keep docs for future website. **Read Sonnet Phase 2 review** — logged
  the CRITICAL login-time 2FA gap as the next top priority, plus caching-wire,
  memoization, N+1, and audit/login retention items.

> Add a new dated bullet each day at wrap-up. Keep bullets to one or two lines;
> the checklists above are the durable state.
