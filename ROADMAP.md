# Zoosper CMS — Master Roadmap

> **Single source of truth for high-level feature status.**
> High-level only — one line per capability, not per micro-phase. Detailed
> phase notes live under `docs/`. Update this file once a day at wrap-up:
> tick completed items, add newly planned ones, move things between sections.

**Last updated:** 2026-07-26 (Sydney)
**Framework baseline:** PHP 8.5 · `marko/framework ^0.8` · Pest/PHPUnit · Psalm · Latte

Legend: `[x]` done & deployed · `[~]` in progress / partial · `[ ]` planned

---

## 1. Core Platform & Architecture

- [x] Modular package layout (`app/zoosper-*`) with per-module `composer.json` + PSR-4
- [x] Service container + module-owned `config/services.php` providers
- [x] Module route loader (admin + API) & controller providers
- [x] Config aggregation from module `config/*`
- [x] Declarative schema engine (validator, migrator, snapshot audit)
- [x] Driver-aware schema detection (MySQL + SQLite) — `PageRepository` fixed
- [x] Entity save lifecycle + listener discovery
- [x] Entity extension (EAV) data persistence
- [x] Event/observer bus (module-discovered listeners)
- [x] Method plugin / interceptor system (report-only seam)
- [x] Core ⇄ feature decoupling (core imports no feature modules)
- [x] Site-lookup boundary: core-owned `SiteLookupInterface` + `NullSiteLookup`
- [x] Frontend fallback handler wiring (fatal fixed) + boot-and-serve test
- [x] Behavioural architecture guard (namespace-ban test)
- [ ] Root autoload contract refinement (stop committing generated root map)
- [ ] Promote `api` / `mail` / `two-factor` / `url-rewrite` / `install` to real packages
- [ ] Single module-home convention (`app/` vs `packages/` vs `modules/`)
- [ ] Container autowiring
- [ ] Module lifecycle (install / enable / disable / uninstall)
- [ ] Module-owned migrations
- [ ] Composer packaging + stability contract / 0.x tag + CHANGELOG

## 2. Sites, Pages & Content

- [x] Multi-site + site domains (store-view model)
- [x] Sites & domains admin CRUD
- [x] Pages CRUD (admin)
- [x] Page revisions
- [x] SEO metadata fields (title/description/keywords/canonical)
- [x] Editor.js content model + JSON save pipeline
- [x] Block JSON → HTML rendering
- [x] HTML sanitization (HTMLPurifier)
- [x] Frontend page rendering via themes
- [~] `content_json` frontend rendering via `PageRenderer` (planned deepening)
- [ ] Router path parameters
- [ ] URL rewrites admin depth

## 3. Themes & Templating

- [x] Latte templating + PHP template engine adapters
- [x] Theme repository + per-site theme selection
- [x] Theme admin (assign theme to site)
- [x] Module/theme template overrides (path-safe)
- [x] Layout update system
- [~] RoleAdmin/UserAdmin → Latte cutover (users on Latte; roles still PHP views)
- [ ] Adopter theme override story documented end-to-end

## 4. Admin & Auth

- [x] Admin authentication + session guard
- [x] Roles, permissions, ACL tree
- [x] Admin users CRUD
- [x] CSRF middleware + auth middleware pipeline (OR-permission semantics)
- [x] Audit log + login history
- [x] Admin navigation / dynamic menu
- [x] Admin form section + processor registries (extensible)
- [x] i18n / translations / admin locale preference
- [x] 2FA (TOTP) enrolment, reset, recovery codes
- [ ] Admin god-module split (move Page/User/Role/Theme controllers into feature modules)
- [ ] Password policy (min length / complexity)

## 5. Security

- [x] Security headers (X-Content-Type-Options, X-Frame-Options, Referrer/Permissions-Policy)
- [x] Session cookie `secure` defaults from request scheme (HTTPS-aware)
- [x] Content-Security-Policy (report-only) + HSTS (HTTPS-only)
- [x] Constant-time authentication (no user-enumeration timing leak)
- [x] Rate-limiting subsystem built (store, guard, policy, report-only middleware)
- [ ] Rate limiting wired live on `/admin/login` (currently `enabled=false`)
- [ ] Password min-length/complexity enforcement
- [ ] Atomic admin writes (transaction-wrap user/role create+sync)
- [ ] Structural email-log body redaction (code-enforced, not comment-only)
- [ ] Host-header allowlist / cache-poisoning mitigation note

## 6. Media

- [x] Media library + admin upload
- [x] Editor.js image integration + upload endpoint
- [x] Local media derivative foundation (copy/no-op processor seam)
- [~] Media standalone package split (in progress)
- [ ] Media derivative processing continuation (resize/transform profiles)

## 7. Mail

- [x] SMTP mailer + logged mailer
- [x] Email log repository + admin viewer
- [x] Mail diagnostics + Mailpit local testing

## 8. API

- [x] API module (Auth, ContentPage, Health, Me controllers)
- [ ] Headless API parity (roles, themes, url-rewrites CRUD)
- [ ] ContentPage API exposes structured Editor.js JSON (not serialized HTML)

## 9. Modular Asset Pipeline

- [x] Asset registry / resolver / controller (path-safe, MIME allowlist, ETag)
- [x] `config/assets.php` auto-discovery per module
- [ ] Wire `/asset/{module}/{path}` route + `asset()` helper live
- [ ] Switch dashboard CSS to `asset('zoosper-admin', …)` (prove E2E)

## 10. Quality, Tooling & Repo Hygiene

- [x] Pest + PHPUnit test harness
- [x] Quality gate runner (`tools/gate.php`) — site-lookup audit + tools hygiene + durable-registry integrity
- [x] Durable-tool manifest consolidated to single `config/durable-tools.php`
- [x] Boot-and-serve feature test (guards the whole boot path)
- [x] Duplicate PageMomentum stack + dead roles views removed
- [x] Tools prune (self-computing, reference-verified)
- [~] Docs kept for now; consolidate to feature-based files later
- [ ] CI workflow (GitHub Actions: validate, Psalm, Pest+coverage, gate on every PR)
- [ ] Psalm in CI (types only; `--find-unused-code` noisy against container)
- [ ] Fix composer `gate` script to use `@php` (not hardcoded `php8.5`)
- [ ] Retire remaining process-artifact test+tool pairs
- [ ] Docs website (from the kept `docs/` corpus)

## 11. Page Momentum (visible admin dashboard)

- [x] Routed `/admin/page-momentum` dashboard with real read-only facts
      (page counts, status breakdown, SEO gaps, url_rewrites)
- [ ] Depth: more cards + 7-day trend deltas (deferred — adopters first)

---

## Daily log (most recent first)

- **2026-07-26** — Frontend fatal fixed + boot test; CSRF roles field fixed;
  PageRepository SQLite fix; site-lookup read/write alignment; duplicate/dead-code
  cleanup; durable-manifest consolidation; core-decoupling test; security batch
  (secure session default, CSP+HSTS, constant-time auth); tools prune; decided to
  keep docs for future website.
- **2026-07-25** — Site-lookup boundary guard → CLI consolidation → quality gate
  + hygiene; durable-tool recovery incident resolved; page-momentum read-only
  facts + styling; modular asset pipeline + auto-discovery.

> Add a new dated bullet each day at wrap-up. Keep bullets to one or two lines;
> the section checklists above are the durable state.
