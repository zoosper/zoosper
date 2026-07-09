# Zoosper CMS

Zoosper is a modern, lightweight, modular PHP 8.5+ CMS inspired by Magento-style extensibility, Hyva-style frontend simplicity and Marko PHP module conventions.

## Current phase

Phase 0.11 — Declarative Schema Engine.

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
- Per-module translation drop files under `config/translations/`
- Admin footer showing configured CMS version
- Declarative schema MVP using module `config/db_schema.php`

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

## Declarative schema

Modules can declare database tables in:

```text
app/<module>/config/db_schema.php
modules/<vendor-module>/config/db_schema.php
```

Check schema changes:

```bash
php bin/zoosper-schema diff
```

Apply safe additive schema changes:

```bash
php bin/zoosper-schema apply
```

The current engine supports safe additive operations only: create missing table, add missing column and add missing indexes.

## Documentation

Detailed architecture notes live in `docs/architecture/` and phase plans live in `docs/roadmap/`.

## Development principle

Keep core small, explicit and AI-friendly. Optional capabilities should live in modules with their own config, routes, menu entries, translations and schema declarations.
