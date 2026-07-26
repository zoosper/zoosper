# Zoosper CMS — Master Roadmap

> **Single source of truth for high-level feature status.**
> High-level only — one line per capability, not per micro-phase. Detailed phase
> notes live under `docs/`. Update this file once a day at wrap-up: tick completed
> items, add newly planned ones, move things between sections.

**Last updated:** 2026-07-25 (Sydney)
**Framework baseline:** PHP 8.5 · `marko/framework ^0.8` · Pest/PHPUnit · Psalm · Latte

Legend: `[x]` done & deployed · `[~]` in progress / partial · `[ ]` planned

---

## 0. TOP PRIORITY — next phase

- [ ] **Login-time 2FA enforcement (CRITICAL).** Enrolled users are NOT challenged
  for a TOTP code at login — `guard->login()` authenticates on password alone;
  2FA is currently a setup nag, not a factor. Implement two-state session
  (`pending2FA` → `authenticated`), `/admin/2fa/challenge` backed by the
  existing `admin_two_factor_challenges` table, recovery-code redemption, and a
  test asserting a 2nd enrolled login cannot authenticate without a valid OTP.
  _(Sonnet Phase 2 §1)_

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
- [ ] Request/process memoization for `ModuleRegistry::enabledModules()` _(§2.2/§3)_
- [ ] Root autoload contract refinement (stop committing generated root map)
- [ ] Promote `api`/`mail`/`two-factor`/`url-rewrite`/`install` to real packages
- [ ] Single module-home convention (`app/` vs `packages/` vs `modules/`)
- [ ] Container autowiring
- [ ] Module lifecycle (install/enable/disable/uninstall) + module-owned migrations
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
- [ ] **2FA enforced at login** (see §0 — CRITICAL)
- [ ] Recovery-code redemption at login
- [ ] Memoize `SessionGuard::user()` per request _(§3.1)_
- [ ] Admin god-module split (Page/User/Role/Theme → feature modules)
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
- [~] Media standalone package split
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
- [ ] Test: 2nd enrolled login cannot authenticate without OTP _(§7)_
- [ ] Docs website (from the retained `docs/` corpus)

## 12. Page Momentum (visible admin dashboard)

- [x] Routed `/admin/page-momentum` with real read-only facts
- [ ] Depth: more cards + 7-day trend deltas (deferred)

---

## Daily log (most recent first)

- **2026-07-25** — Frontend fatal fixed + boot test; PageRepository SQLite fix;
  site-lookup read/write alignment; CSRF roles field fixed; duplicate/dead-code
  cleanup; durable-manifest consolidation; core-decoupling test; security batch
  (secure session default, CSP+HSTS report-only, constant-time auth); tools prune;
  decided to keep docs for future website. **Read Sonnet Phase 2 review** — logged
  the CRITICAL login-time 2FA gap as the next top priority, plus caching-wire,
  memoization, N+1, and audit/login retention items.

> Add a new dated bullet each day at wrap-up. Keep bullets to one or two lines;
> the checklists above are the durable state.
