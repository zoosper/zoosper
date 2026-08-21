# Changelog

## [0.3.0-alpha.3-dev]

- Completed Phase 10AP-A Media reads and derivatives: feature-owned PAT reads now provide bounded collection pagination, allow-listed search/filter/sort controls, deterministic derivative metadata, and private-storage-path redaction.

### Planned
- Visible CMS capability and extension-author experience.


## [0.3.0-alpha.2] - 2026-08-19

### Added
- Feature-owned PAT-scoped API expansion and Page lifecycle/revision foundations.
- Dedicated `zoosper/cache`, `zoosper/config`, and `zoosper/scoped-config` packages.
- Stronger stateless HTTP, media lifecycle, authentication, and operational recovery coverage.

### Changed
- Shared cache, Marko configuration compatibility, and persisted scoped configuration now have honest package owners.
- API controllers and lifecycle orchestration continue moving into owning feature modules.
- Documentation, package READMEs, architecture decisions, and module-manifest verification were updated throughout the development line.

### Removed
- Unused Zoosper-native general method-plugin subsystem.

### Verified
- 1,472 tests with 8,672 assertions, zero quality findings, fresh installation, frontend boot, valid Composer manifests, and a fresh 34-module manifest.

## v0.3.0-alpha.1

### Added

- Stateless HTTP gateway with RFC-aware 404/405 handling, implicit HEAD, stateless OPTIONS and exact-origin CORS.
- Auth-owned hash-only Personal Access Tokens with scopes, expiry, revocation, last-used metadata and stateless bearer identity.
- PAT-scoped Page reads, mutations, publication, revision listing and restoration.
- PAT-scoped Menu reads, mutations, item operations and guarded lifecycle operations.
- Canonical Media ingest, upload-time WebP derivatives and persisted derivative records.

### Changed

- Page and Menu API routes, adapters, factories, tests and dependencies moved into their owning feature modules.
- Shared Admin and API mutations use feature-owned application services.
- The API platform no longer depends on Page or Menu feature implementations.

### Quality

- 1,441 tests with 8,237 assertions.
- Standard quality gate completed with zero errors and zero warnings.
- Fresh validated 28-module manifest.



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

### PAT Admin lifecycle
- Added authenticated self-service PAT issuance, one-time secret display, listing, revocation and audit-safe lifecycle records.

- Added stateless PAT-authenticated Page list/detail API reads with Site isolation and structured content.

- Added shared Page save application ownership and stateless PAT-scoped Page create/update endpoints.
