## Zoosper CMS

Latest pre-release: `v0.1.0-alpha.1`  
Required runtime: PHP 8.5+

Zoosper is a modern, lightweight, modular PHP 8.5+ CMS inspired by
Magento-style extensibility, Hyva-style frontend simplicity and Marko PHP
module conventions.

### Current phase

Public alpha hardening and launch-readiness — two
confirmed security issues from external review (privilege escalation on
`/admin/users/edit`, a rate-limit store race condition) fixed and verified;
a real production 2FA lockout incident diagnosed and fixed with proper key
rotation support; a module-manifest compile step built (and a stale-cache
bug it introduced caught and fixed by a second review pass); the first
module (`zoosper/errors`) extracted out of `zoosper-core` into its own
standalone package with **real, verified** Marko framework integration
(not just an unused dependency); and a working, configurable (file/Redis)
cache foundation wired in, with an opt-in frontend page cache built on top
of it. See [ROADMAP.md](ROADMAP.md) for full, current status — it is kept
genuinely up to date and is the project's actual continuity mechanism.

### Framework foundation

Zoosper builds on real, adopted [Marko PHP](https://marko.build) packages
where they fit — checked and verified against actual installed source
before adoption, not assumed from package names. Currently adopted:
`marko/core` (`MarkoException`, extended by `ZoosperException`),
`marko/errors` + `marko/errors-simple` (real error reporting and CLI/web
display), and `marko/cache` + `marko/cache-file` + `marko/cache-redis`
(a real, configurable cache backend). Evaluated and deliberately deferred:
`marko/database` (too large a rewrite of the existing schema/persistence
layer to adopt casually). See [ROADMAP.md §14](ROADMAP.md) for the full,
current adoption strategy and reasoning.

### What is included

- All first-party modules registered as real Composer packages, with per-module dependency resolution
- Two modules (`zoosper/errors`, `zoosper/media`) fully extracted into standalone `packages/` (more planned — see roadmap)
- Module-owned database migrations (each module owns its own schema history), always resolved via live module discovery (never a stale compiled cache)
- Module-owned controller providers through `config/controllers.php`
- Module-owned admin/API routes, menus, ACL/resource config and views
- Module-owned log filenames through `config/logging.php`
- Console commands (`admin:create`, `site:create`, `page:create`) discovered per-module, not hardcoded in the CLI kernel
- A module-manifest compile step (`bin/zoosper compile`/`cache:clear`/`deploy`) for faster boot, with safe fail-back to a live scan
- Admin form UI metadata through `config/admin_ui.php`
- Admin grid pagination/search/filter foundation, with genuinely honored per-column sorting
- Real, opt-in frontend page caching (file or Redis backend, disabled by default) — see [ROADMAP.md §10](ROADMAP.md)
- Layout updates with remove, replace and inject operations
- Login-time 2FA enforcement with recovery-code redemption and real encryption-key rotation support
- A hardened security baseline: real server-side MySQL prepared statements, pinned table collation, rate-limit identity salting, an environment-guarded HTML sanitizer fallback, and a fixed admin privilege-escalation path
- PCI-aware roadmap notes

### Pages grid filters

`/admin/pages` supports the foundation for: `q`, `status`, `site_id`,
`page`, `page_size`. The controller integration remains module-owned in
`zoosper-page`.

### Getting started

```bash
composer install
cp .env.example .env
# Edit .env: APP_KEY, TWO_FACTOR_ENCRYPTION_KEY, database, mail as needed
php bin/zoosper migrate
```

See [docs/guide/getting-started.md](docs/guide/getting-started.md) for the
full walkthrough, and [.env.example](.env.example) for every documented
environment variable.

### Security

See [SECURITY.md](SECURITY.md) for the vulnerability disclosure policy.
This project is under active, pre-release development — see
`SECURITY.md` for exactly what that means for support and reporting.

### Documentation

Canonical feature guides: [docs/guide/index.md](docs/guide/index.md).

### Roadmap

See [ROADMAP.md](ROADMAP.md) — kept genuinely current, not just at
milestones. It is the project's actual continuity mechanism: a fresh
conversation or a new contributor should be able to reconstruct full
project state from this file alone.

## Continuous integration

Changes targeting `dev` run strict Composer validation, locked dependency audit, shipped JavaScript syntax validation, the strict repository gate, an explicitly advisory Psalm baseline, the full Pest suite and module compilation. Psalm remains visible but non-blocking until its pre-existing baseline is reduced to zero.

## Current phase

Zoosper CMS is in active public-alpha development. The latest tagged pre-release is `v0.1.0-alpha.1`. The current `dev` branch includes the first usable modular CMS foundation, while destructive entity lifecycle, referential-integrity, and several production-security items remain explicit launch blockers rather than completed capabilities.

Recent delivered work includes:

- a GitHub Actions quality gate with Composer validation, dependency audit, JavaScript syntax checks, repository checks, Psalm visibility, the full Pest suite, module-manifest compilation, release checks, and a fresh-install smoke test;
- a tracked pre-push hook running the full Pest suite and the standard Zoosper quality gate;
- the `zoosper-menu` module with Admin CRUD, site-scoped nested menus, frontend navigation, breadcrumbs, API output, ACL, safe URL handling, and delete support;
- Page revisions with history, preview, audited restore, and restore-before-change safety capture;
- a zero-dependency static documentation site built from the canonical `docs/` source;
- Marko View and Marko Admin contract adoption for frontend rendering, Admin menu items, sections, metadata, and the live Admin navigation runtime;
- one co-located technical root README for every first-party Composer module and package;
- substantial repository cleanup across historical tools, phase fragments, duplicated documentation, and production Page Momentum scaffolding.

## What is included

- Modular Composer packages discovered from application and package layers.
- Multi-site resolution with request-carried site context.
- Secure Admin authentication, ACL, CSRF protection, audit logging, login history, and optional two-factor authentication infrastructure.
- Page management, structured content rendering, revisions, preview, restore, SEO metadata, and frontend theme rendering.
- Site, domain, URL rewrite, settings, mail, media, menu, Grid, API Grid, Admin Grid, and Store Orders modules or packages at their documented maturity levels.
- API-first architecture with Latte as the current default template engine and Marko View contracts at selected runtime boundaries.
- Module-owned routes, services, migrations, schema, permissions, menu items, Admin sections, assets, settings, events, entity-save listeners, tests, and technical documentation.
- CI, pre-push verification, release checks, a standard quality gate, and a static documentation website.

### Explicitly not complete

- Core entity archive/delete flows are not yet consistently available outside Menu.
- Declarative-schema foreign-key support and broader referential-integrity enforcement remain open.
- Media derivative processing is not wired to a production processor and enablement policy.
- Rate limiting is report-only; enforcement is not yet active.
- Password policy, automatic password rehash, production fail-closed secure-session defaults, and the stateful `/api/*` CSRF decision remain open.
- Grid and Admin Form extension models have not yet been consolidated across every Admin screen.
- Psalm remains advisory until a baseline and no-new-errors policy are established.


### Application-owned sessions

Zoosper Core depends only on native `SessionHandlerInterface`. The `zoosper/session` module currently adapts `marko/session-file` and owns the third-party dependency. Sessions default to application-owned `var/sessions`, configurable through `SESSION_STORAGE_PATH`; no host PHP session-path change is required.

## Starter site
Run `php bin/zoosper starter:install` for an idempotent minimal Site with published Home and About Pages rendered by the default starter theme.

- `zoosper-seo`: extensible metadata, sitemap and robots orchestration with module-discovered feature contributors.

### API authentication security
The session-based API login is throttled through the canonical authentication limiter and refuses password-only session creation for accounts with active 2FA.

### Media ingest security
Supported raster uploads are canonicalised through GD before storage and publication; user-supplied image bytes are not copied directly into `public/media`.

### Media derivatives
Canonical originals now generate upload-time GD WebP derivatives through the Media-owned processor contract. Default profiles are thumb, medium, and large; generation is fail-closed and files are written atomically.

### Media derivative records
Generated Media profiles are first-class database records with Media-owned lookup and permanent-delete cleanup.

### HTTP gateway
Public API reads now declare stateless execution, wrong-method requests return RFC-compliant 405 responses, and exact-origin CORS preflight is configurable. Production boot fails closed for insecure session or authentication-throttling settings.
