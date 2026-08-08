## Zoosper CMS

Zoosper is a modern, lightweight, modular PHP 8.5+ CMS inspired by
Magento-style extensibility, Hyva-style frontend simplicity and Marko PHP
module conventions.

### Current phase

Post-Phase 1.41 hardening and Marko adoption (2026-07-30/31) — two
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
