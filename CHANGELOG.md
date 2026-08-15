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

### Redirect and stateless public foundations
- Added 301/302 redirect validation, reserved-path protection, unsafe-scheme rejection, and direct-loop prevention.
- Added Site-scoped URL Rewrite listing and save operations.
- Made sitemap and robots requests stateless before session initialisation.

### URL Rewrite frontend adoption
- Added module-discovered service decorators and URL Rewrite fallback composition ahead of Page fallback.
- Added Site-scoped redirect cycle and maximum-depth diagnostics.

### API authentication security
- Prevented password-only API session creation for Admin accounts protected by 2FA.
- Applied canonical password-login throttling to `/api/v1/auth/login`, including JSON 429 and `Retry-After`.

### Media upload security
- Canonically re-encode JPEG, PNG, WebP and GIF uploads through GD before storage and publication.
- Added a 40-megapixel decode ceiling and regression guards against direct temporary-upload copying.

### Media derivatives
- Enabled upload-time GD WebP generation for the existing thumb, medium, and large profiles.
- Added bounded resize/crop behaviour, no upscaling, alpha preservation, atomic publication, and fail-closed upload integration.

### Media derivative persistence
- Persist generated profile dimensions and paths as Media-owned records.
- Added profile lookup, upload rollback, cascading metadata ownership, and permanent-delete cleanup for derivative files.

### HTTP gateway foundation
- Added route-owned stateless APIs, RFC 405/Allow handling, configurable exact-origin CORS and production fail-closed security defaults.

### Personal Access Token foundation
- Added hash-only PAT persistence, scoped issuance, revocation, active-owner authentication and a stateless bearer identity endpoint.
