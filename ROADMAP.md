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

- No outstanding CRITICAL security item at this time.
- Highest-value remaining candidates (not yet started, pick one next):
  - CI workflow (validate, Psalm, Pest+coverage, gate on every PR) — cheapest
    high-impact item outstanding; currently quality gates only run when
    remembered locally. _(§11)_
  - Wire or remove the inert HTTP caching subsystem — built but pages ship no
    cache headers. _(§10)_
  - Admin god-module split, remaining piece: `ThemeAdminController` still
    lives in `zoosper-admin` (Page/User/Role already relocated). _(§4)_
  - Decide, when ready, whether to move rate limiting from report-only to
    enforcing mode — requires reviewing real report-only data first per
    the ADR (see §5).

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
- [x] All 12 modules promoted to real Composer packages (Phase 1.40)
- [x] Module-owned migrations (Phase 1.40c)
- [x] Console/kernel decoupling — `admin:create`/`site:create`/`page:create`
  discovered per-module via `ModuleConsoleCommandLoader`, not hardcoded in
  `bin/zoosper`
- [~] Admin/module dependency decoupling (Phase 1.41): two-factor (full),
  media (full), page (partial — 9/11 admin-form classes relocated to
  `Zoosper\Core\Form`; `AdminFormConfigAggregator` +
  `AdminConfigLayeredFileLoader` deliberately left in `zoosper-admin`, see
  `docs/development/phase-1-41-admin-module-decoupling.md`)
- [ ] Request/process memoization for `ModuleRegistry::enabledModules()` _(§2.2/§3)_
- [ ] Single module-home convention (`app/` vs `packages/` vs `modules/`)
- [ ] Container autowiring
- [ ] Module lifecycle (install/enable/disable/uninstall) — migrations are now
  module-owned, but enable/disable/uninstall hooks and per-module version
  tracking are not yet built
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
- [x] 2FA enforced at login (Phase 1.107), with recovery-code redemption
- [x] Login history recording bug fixed (Phase 1.113)
- [ ] Memoize `SessionGuard::user()` per request _(§3.1)_
- [~] Admin god-module split — Page/User/Role admin controllers already
  relocated to their owning modules (Phase 1.33d / Phase F1); `ThemeAdminController`
  not yet moved.
- [x] **Batch-load permissions in `AdminUserRepository` (fix N+1)** — already
  done (Phase 1.109): `all()`/`search()` batch-load via a single
  `WHERE user_id IN (...)` query; `allForAssignment()` skips permission
  loading entirely where not needed. _(§4.1 — corrects a prior roadmap
  entry that had this listed as outstanding)_
- [ ] Pagination + retention for audit log & login history _(§4.2)_

## 5. Security

- [x] Baseline security headers (X-Content-Type-Options, X-Frame-Options, Referrer/Permissions)
- [x] Session cookie `secure` defaults from request scheme (HTTPS-aware)
- [x] Content-Security-Policy (report-only) + HSTS (HTTPS-only)
- [x] Constant-time authentication (no user-enumeration timing leak)
- [x] Rate-limiting subsystem built (store, guard, policy, report-only middleware)
- [x] **Rate limiting wired onto `/admin/login`, report-only mode** — new
  `RateLimitReportOnlyAdminMiddleware` correctly implements `RouteMiddleware`
  and wires the existing engine into the real admin middleware pipeline
  for the first time. Ships disabled by default
  (`app/zoosper-core/config/rate_limit.php`: `enabled: false`) — zero
  behaviour change until explicitly turned on, and even when enabled,
  never blocks a request (report-only diagnostics only), per
  `docs/architecture/adr-rate-limiting-report-only-to-enforcement.md`.
  A previously-scaffolded, never-run tool
  (`tools/apply-rate-limit-admin-middleware-hook.php`) would have inserted
  a malformed closure causing a full admin lockout if ever run with
  `--apply` — flagged in the new middleware's docblock; that tool should
  not be run.
- [ ] Enable report-only mode in production config and begin collecting real
  data — precondition (per the ADR) before any future move to enforcement
- [ ] CSP report-only → enforce (after tuning) _(§5)_
- [ ] Password min-length/complexity + `password_needs_rehash()` upgrade path _(§5)_
- [ ] Prod fail-closed when `SESSION_SECURE` unset _(§5)_
- [ ] CSRF decision for stateful `/api/*` session routes _(§5)_
- [x] **Atomic admin writes (transaction-wrap user/role create+sync)** —
  `RoleRepository::createRole()`/`updateRole()` and
  `AdminUserRepository::createWithRoleIds()`/`updateUser()` now wrap their
  full write sequence in a transaction. **Correction**: this item's
  history had incorrectly implied `AdminUserRepository` was already fixed
  in an earlier phase — on inspection it had the same unwrapped pattern as
  `RoleRepository`; both are now genuinely fixed together, verified with a
  test that forces a real mid-transaction failure and confirms the
  database rolls back completely (not just that an exception was caught).
- [ ] Structural email-log body redaction (code-enforced)
- [ ] `entity_extension_values` write-time field validation at repo boundary _(§6)_
- [ ] Truncate `user_agent` in audit/login-history _(§6)_

## 6. Media

- [x] Media library + admin upload
- [x] Editor.js image integration + upload endpoint
- [x] Local media derivative foundation (copy/no-op processor seam)
- [x] Media standalone package split — `zoosper-media`'s `composer.json` no
  longer requires `zoosper/admin` (Phase 1.41)
- [x] **Fixed: `MediaAdminController::upload()` silently swallowed all
  failures** — a rejected upload (wrong type, too large, corrupt file)
  previously redirected to `/admin/media` with zero feedback, even though
  the code to show a proper error message (`uploadErrorResponse()`)
  already existed and simply wasn't being called. Independently flagged
  by two reviewer passes.
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
  the existing 2FA challenge test suite
- [ ] Docs website (from the retained `docs/` corpus)

## 12. Page Momentum (visible admin dashboard)

- [x] Routed `/admin/page-momentum` with real read-only facts
- [ ] Depth: more cards + 7-day trend deltas (deferred)

---

## Daily log (most recent first)

- **2026-07-29** — Wired the existing, fully-built report-only rate-limit
  engine onto `/admin/login` for the first time (new
  `RateLimitReportOnlyAdminMiddleware`, correctly implementing
  `RouteMiddleware`; zero behaviour change today, ships disabled by
  default, never blocks even if enabled — per the project's own ADR).
  Along the way, found and flagged a previously-scaffolded, never-run tool
  that would have caused a full admin lockout if ever applied. Fixed a
  real, independently-flagged user-facing bug: `MediaAdminController::upload()`
  silently swallowed all upload failures instead of showing the real error
  message. Fixed non-atomic writes in `RoleRepository` AND
  `AdminUserRepository` (both had the same bug — corrected an inaccurate
  claim in two separate reviewer passes that `AdminUserRepository` was
  "already fixed"; it wasn't), verified with tests that force a real
  mid-transaction failure and confirm the database rolls back completely.
  Also corrected another stale roadmap claim: `AdminUserRepository`'s N+1
  permission-loading fix (§4.1) was already done in Phase 1.109, not
  outstanding as previously listed.
- **2026-07-29 (earlier)** — Corrected a significant roadmap/code drift:
  login-time 2FA enforcement (previously listed as the #1 CRITICAL
  outstanding item) was found to already be implemented (Phase 1.107),
  along with recovery-code redemption and a login-history bugfix (Phase
  1.113). Completed Phase 1.41 (admin/module dependency decoupling) for
  two-factor (full) and media (full), partially for page. Completed Phase
  1.40 (all 12 modules real Composer packages), Phase 1.40c (module-owned
  migrations), and console/kernel decoupling.
- **2026-07-25** — Frontend fatal fixed + boot test; PageRepository SQLite fix;
  site-lookup read/write alignment; CSRF roles field fixed; duplicate/dead-code
  cleanup; durable-manifest consolidation; core-decoupling test; security batch
  (secure session default, CSP+HSTS report-only, constant-time auth); tools prune;
  decided to keep docs for future website. Read Sonnet Phase 2 review — logged
  the (later found to be already-fixed) login-time 2FA gap as next priority,
  plus caching-wire, memoization, N+1, and audit/login retention items.

> Add a new dated bullet each day at wrap-up. Keep bullets to one or two lines;
> the checklists above are the durable state.
