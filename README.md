# Zoosper CMS

Zoosper is a modern, lightweight, modular PHP 8.5+ CMS inspired by Magento-style extensibility, Hyva-style frontend simplicity and Marko PHP module conventions.

## Current phase

Phase 1.41 — Admin/module dependency decoupling (two-factor and media modules fully decoupled from the admin module; page module partially decoupled). See [ROADMAP.md](ROADMAP.md) for full status.

## What is included

- All first-party modules registered as real Composer packages, with per-module dependency resolution
- Module-owned database migrations (each module owns its own schema history)
- Module-owned controller providers through `config/controllers.php`
- Module-owned admin/API routes, menus, ACL/resource config and views
- Module-owned log filenames through `config/logging.php`
- Console commands (`admin:create`, `site:create`, `page:create`) discovered per-module, not hardcoded in the CLI kernel
- Admin form UI metadata through `config/admin_ui.php`
- Admin grid pagination/search/filter foundation
- Pages admin grid query service
- Frontend and admin theme foundations
- Layout updates with remove, replace and inject operations
- Login-time 2FA enforcement with recovery-code redemption
- PCI-aware roadmap notes

## Pages grid filters

`/admin/pages` supports the foundation for:

```text
q
status
site_id
page
page_size
```

The controller integration remains module-owned in `zoosper-page`.

## Documentation

Canonical feature guides: [docs/guide/index.md](docs/guide/index.md).

## Roadmap

See [ROADMAP.md](ROADMAP.md).
