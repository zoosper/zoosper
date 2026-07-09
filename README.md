# Zoosper CMS

Zoosper is a modern, lightweight, modular PHP 8.5+ CMS inspired by Magento-style extensibility, Hyva-style frontend simplicity and Marko PHP module conventions.

## Current phase

Phase 0.13 — Theme and Template Rendering.

## What is included

- Modular application structure under `app/` and `modules/`
- Dynamic module discovery through `module.php`
- Dynamic admin menu discovery through module `config/admin_menu.php`
- Dynamic admin/API route discovery through module route config files
- Admin login/logout with CSRF protection and secure password hashing
- Admin users, roles and permission matrix
- Magento-style grouped ACL tree for permissions
- Assign admin users directly from the role editor
- Audit log and login history foundations
- Multisite/domain-based site resolution
- CMS page CRUD, preview, publish and unpublish
- Frontend rendering for `/` and page slugs like `/home`
- Theme and template rendering through `zoosper-theme`
- Default frontend theme under `themes/default`
- Per-module translation drop files under `config/translations/`
- Central CMS version service used by admin and page rendering
- Declarative schema engine using module `config/db_schema.php`
- Declarative schema validation and schema snapshots

## Quick start

```bash
cp .env.example .env
composer install
php bin/zoosper migrate
php bin/zoosper admin:create --email=admin@example.com --password='ChangeMe123!' --name='Admin User'
php bin/zoosper site:create --code=main --name='Main Website' --host=localhost
php bin/zoosper page:create --site=main --title='Home' --slug=home --content='Welcome to Zoosper.'
php -S 127.0.0.1:8080 -t public
```

## Useful routes

```text
/
/home
/admin/login
/admin
/admin/pages
/admin/users
/admin/roles
/admin/audit-log
/admin/login-history
/api/v1/health
/api/v1/content/page?slug=home
```

## CMS version

Set the displayed CMS version in `.env`:

```env
CMS_VERSION=0.13.0-dev
```

The version is read through `Zoosper\Core\App\CmsVersion` and displayed in the admin footer and frontend page footer.

## Themes

The default theme lives in:

```text
themes/default/
```

The first frontend template is:

```text
themes/default/templates/page.php
```

Public theme assets are served from:

```text
public/themes/default/assets/
```

## Declarative schema

Validate declarations:

```bash
php bin/zoosper-schema validate
```

Check schema changes:

```bash
php bin/zoosper-schema diff
```

Apply safe additive schema changes:

```bash
php bin/zoosper-schema apply
```

View applied schema snapshots:

```bash
php bin/zoosper-schema snapshots
```

## Documentation

Detailed architecture notes live in `docs/architecture/` and phase plans live in `docs/roadmap/`.

## Development principle

Keep core small, explicit and AI-friendly. Optional capabilities should live in modules with their own config, routes, menu entries, translations, schema declarations and templates.
