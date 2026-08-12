# Changelog


## 0.3.0-alpha.1-dev

### Development line opened
- Opened the Content and Marketing alpha line after v0.2.0-alpha.1.
- Planned scope: SEO presentation, sitemap and robots, redirect management, forms, and focused block-editor improvements.

## 0.2.0-alpha.1 - 2026-08-12

Development line after the `v0.1.0-alpha.1` release. Planned emphasis: useful CMS core, visible Admin and content momentum, richer Media capability, revisions, menus, seed content, starter-theme improvements and continued release-contract hardening.

## 0.1.0-alpha.1

Initial alpha candidate. Includes modular installation and migrations, Admin authentication and two-factor support, site-aware page rendering, Settings workspace, Media foundation, Store Orders workspace, module-owned assets, CLI deployment/recovery commands, CI quality gates and release-readiness diagnostics.

Known limitation: Psalm remains advisory while the existing baseline is reduced. Alpha APIs and extension contracts may change before stable release.

- Added the full Page revision snapshot domain with bounded retention and fresh-install schema coverage.

- Removed the internal Page Momentum launch-readiness dashboard from the production Admin surface.

- Consolidated module and package documentation into concise current READMEs and removed integration patch notes, readiness stubs and historical Media phase documents.

- Removed completed root apply scripts, package-local Media migration/audit tooling, tool-only historical tests and tracked runtime Media artefacts.

- Made `type: zoosper-module` the sole public module identity and centralised private upstream Marko compatibility across Composer and runtime discovery.

- Added Page revision history, historical preview, complete snapshot capture and CSRF-protected safe restore with audit logging.

- Added a zero-dependency static documentation website that builds `docs.zoosper.com` directly from canonical repository Markdown.

- Unified Zoosper branding across the documentation site, Admin shell and default frontend theme using one Theme-owned vector mark and explicit public runtime assets.

### Phase 9IF Media lifecycle truth closure
Media assets now use POST-only, media.manage, CSRF-protected archive, restore and archived-first permanent deletion boundaries. Metadata deletion is transactional and owned-file cleanup is conservative and audited. Upload derivatives remain disabled by default; LocalCopyMediaProcessor is not image transformation support.

### Starter experience and release readiness
- Added safe repeatable starter Site and Page installation.
- Added starter-theme and application-owned session checks to release diagnostics.

### SEO presentation foundation
- Added frontend Page metadata resolution with safe canonical policy, preview noindex behaviour, and equivalent PHP/Latte head rendering.
- Added escaped description, robots, canonical and Open Graph basics to the default theme.

### Menu item update hotfix
- Fixed update-only PDO parameters so editing an existing Menu item no longer sends the insert-only `created_at` parameter to the update statement.
- Added SQLite-backed coverage for Page-linked, top-level, position-zero Menu item updates.

### Sitemap and robots foundation
- Added public Site-aware `/sitemap.xml` and `/robots.txt` endpoints with correct content types.
- Sitemap output includes only published Pages, escapes XML and refuses untrusted request-host URL generation.

### Dedicated SEO module extraction
- Moved metadata values, contributor discovery, sitemap aggregation, robots output and public routes into `zoosper-seo`.
- Page now declares Page metadata and published Page sitemap contributors through module-owned `config/seo.php`.
