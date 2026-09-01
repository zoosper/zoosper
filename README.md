# Zoosper CMS

**A modular, API-first CMS without the weight. Extend features without forking core.**

- Latest pre-release: `v0.3.0-alpha.3`
- Current development line: `0.3.0-alpha.4-dev`
- Required runtime: PHP 8.5+

Zoosper is an API-first, multi-site CMS built around independently owned Composer modules. Each feature can contribute its own routes, services, schema, migrations, permissions, Admin UI, API adapters, tests, assets and documentation. Cross-cutting platform modules stay free of feature implementation dependencies.

> Zoosper is public alpha software. It is suitable for evaluation, extension development and controlled testing. No stable release has shipped.

## Why Zoosper

- **Feature-owned architecture:** Page, Menu, Media, Site, Auth, SEO and other capabilities own their runtime contributions.
- **Dependency-aware modules:** first-party modules are Composer packages discovered through the module registry.
- **API-first delivery:** stateless bearer APIs coexist with the Admin and frontend rendering layers.
- **Extension without core edits:** modules contribute routes, services, events, settings, forms, Grid columns, assets and presentation.
- **Multi-site by design:** resolved Site context travels immutably with each request.
- **Security-focused foundations:** ACL, CSRF, 2FA, password policy, automatic password rehash, rate limiting, secure sessions, canonical Media ingest and safe audit metadata.
- **Pluggable presentation:** Latte is the current default template engine; Marko View contracts are adopted at selected boundaries.

## What shipped in v0.3.0-alpha.3

### Integration APIs

- Auth-owned, hash-only Personal Access Tokens with scopes, expiry, revocation and last-used metadata.
- Stateless bearer identity with current-owner permission intersection.
- Page list, detail, create, update, publish, unpublish, revision listing and revision restoration.
- Menu list, detail, resolved tree, Menu and item mutations, guarded item deletion, disable, restore and guarded permanent deletion.
- Feature-owned Page and Menu API routes, adapters, controller factories and tests. The cross-cutting `zoosper-api` module does not own those feature implementations.

### CMS and platform

- Multi-site Page management with structured Editor.js content, generated HTML, SEO metadata, revisions, preview and lifecycle controls.
- Site-scoped nested Menus, frontend navigation and breadcrumbs.
- Canonical raster Media ingest, upload-time WebP derivatives, persisted derivative metadata and lifecycle cleanup.
- Extensible SEO metadata, sitemap and robots orchestration.
- Application-owned file sessions behind `SessionHandlerInterface`.
- Module-owned migrations, declarative schema, ACL, Admin routes, API routes, controller factories, services, settings, assets, events and tests.
- Admin Grid workspaces, saved views, column visibility and ordering, filtering, paging, export and protected bulk-action foundations.

### HTTP and security

- RFC-aware `404` and `405` handling, `Allow`, implicit `HEAD`, stateless `OPTIONS` and configurable exact-origin CORS.
- Login-time 2FA with recovery codes and encryption-key rotation support.
- Password policy and successful-login password rehash upgrades.
- Authentication throttling, production fail-closed security checks, hardened session policy and real server-side prepared statements.
- Canonical GD re-encoding so uploaded raster bytes are not copied directly into public Media storage.

## Architecture at a glance

```text
zoosper-api       Cross-cutting API platform and authentication endpoints
zoosper-auth      Identity, ACL, sessions guards, PATs and password security
zoosper-page      Page domain, Admin UI, frontend rendering and Page APIs
zoosper-menu      Menu domain, Admin UI, frontend navigation and Menu APIs
zoosper-media     Media ingest, processing, derivatives and lifecycle
zoosper-seo       Metadata, sitemap and robots contributor orchestration
zoosper-site      Site and domain ownership
zoosper-theme     Pluggable template-engine and theme runtime
zoosper-core      Framework contracts, HTTP, routing, module discovery and shared infrastructure
```

A feature-owned API slice follows this pattern:

```text
<module>/
├── config/api_routes.php
├── config/controllers.php
├── src/Api/
├── src/Application/
└── tests/Unit/Api/
```

Removing or disabling a feature module removes its discovered routes and factories. Required dependencies remain Composer-enforced.

## System requirements and PHP 8.5+ runtime

Zoosper targets PHP 8.5+ as its language and runtime floor. The architecture leverages modern language capabilities including constructor property promotion, typed properties, readonly classes, first-class callables, pattern matching expressions, enhanced type systems, and forward compatibility with Marko framework packages.

### Minimum requirements

- **PHP:** `^8.5` (`php8.5` CLI and web SAPIs)
- **Required PHP extensions:**
  - `pdo` and `pdo_sqlite` (for local development, fast testing, and single-tenant installs)
  - `pdo_mysql` (for production MySQL / MariaDB environments)
  - `json` (for Editor.js structured content, JSON API endpoints, and configuration manifests)
  - `gd` (for canonical raster image re-encoding and derivative processing)
  - `curl` (for external API grid adapters and webhook transports)
  - `mbstring` (for UTF-8 string manipulation and internationalization)
- **Database engines:**
  - SQLite 3.35+ (development and local testing)
  - MySQL 8.0+ or MariaDB 10.6+ with `utf8mb4_unicode_ci` collation (staging and production)
- **Web server / SAPI:**
  - Built-in PHP development server, Caddy, Nginx + PHP-FPM, or Apache with URL rewriting enabled

## Getting started

```bash
composer install
cp .env.example .env
php8.5 bin/zoosper migrate
php8.5 bin/zoosper starter:install
```

The example is safe for the documented local HTTP server: Secure cookies and login throttling remain disabled until explicitly configured. Configure the database, `APP_KEY`, `TWO_FACTOR_ENCRYPTION_KEY` and deployment-specific settings before boot. Staging and production require HTTPS Secure cookies, enforced rate limiting and a strong `RATE_LIMIT_IDENTITY_SALT`.

Useful commands:

```bash
php8.5 bin/zoosper version
php8.5 bin/zoosper compile
php8.5 bin/zoosper module:manifest:status
php8.5 bin/zoosper module:manifest:check
php8.5 vendor/bin/pest
php8.5 tools/gate.php
```

See [Getting started](docs/getting-started.md), the [documentation index](docs/README.md), [release checklist](docs/release-checklist.md), [security policy](SECURITY.md), [changelog](CHANGELOG.md) and [roadmap](ROADMAP.md).

## Extension model

A Zoosper module can own or contribute:

- Composer dependencies and PSR-4 namespaces
- services and interface implementations
- Admin and API routes
- database schema and migrations
- ACL permissions and Admin navigation
- settings catalogue entries
- events and entity-save listeners
- Admin form sections and processors
- Grid columns, filters, saved-view behaviour and bulk actions
- templates, frontend navigation, assets and translations
- tests and package-level technical documentation

Use `php8.5 bin/zoosper make:module` for a local module or the package-module scaffolder for a distributable package.

## Quality and release discipline

The `v0.3.0-alpha.3` release gate completed with:

- 1,557 passing tests
- 11,175 assertions
- zero strict quality-gate errors
- zero strict quality-gate warnings
- a compiled, fresh module manifest
- a disposable fresh-install alpha smoke test
- browser acceptance and production-safe console boot

CI and the tracked pre-push hook run the repository quality contract. Psalm remains visible and advisory while its existing baseline is reduced.

## Latest release and current development focus

`v0.3.0-alpha.3` completes the Media API lifecycle, reconciles module-discovery collision truth, delivers the responsive Admin workspace and navigation refinement, and preserves deployment-provided environment values ahead of `.env`. Development is now open as `0.3.0-alpha.4-dev`; staging and production continue to fail closed for secure sessions and enforcing rate limits across HTTP and console boot.

### Explicitly not complete

- Referential-integrity and foreign-key reconciliation are not yet comprehensive across every cross-module reference.
- CI test suite execution against an active MySQL service container is being finalized alongside SQLite runs.
- Static analysis (Psalm) remains advisory while the baseline is reduced toward an enforced zero-baseline gate.
- Automated secret generation and comprehensive boot-time production validation are being finalized.
- Absolute session lifetime controls and concurrent session limits are in progress.

## Project status and support

Zoosper CMS is in active public-alpha development. The latest tagged pre-release is `v0.3.0-alpha.3`, and the current development line is `0.3.0-alpha.4-dev`. Review [SECURITY.md](SECURITY.md) before reporting a vulnerability and [ROADMAP.md](ROADMAP.md) for current continuity and planned work.

## Licence

See the repository licence for usage terms.
