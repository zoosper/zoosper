# Zoosper CMS

Zoosper is a modern, lightweight, modular PHP 8.5+ CMS inspired by Magento-style extensibility, Hyva-style frontend simplicity and Marko PHP module conventions.

## Current phase

Phase 0.16 — Module Template Overrides and Admin Theme Foundation.

## What is included

- Modular application structure under `app/` and `modules/`
- Dynamic module discovery through `module.php`
- Dynamic admin menu discovery through module `config/admin_menu.php`
- Dynamic admin/API route discovery through module route config files
- Module-owned controller providers through `config/controllers.php`
- Admin login/logout with CSRF protection and secure password hashing
- Admin users, roles and permission matrix
- Magento-style grouped ACL tree for permissions
- Assign admin users directly from the role editor
- Audit log and login history foundations
- Multisite/domain-based site resolution
- Per-site `theme_code` field
- Theme admin screen at `/admin/themes`
- Frontend theme template override lookup
- Module template rendering using `module::path`
- Admin theme foundation under `themes/admin/default`
- CMS page CRUD, preview, publish and unpublish
- Declarative schema engine using module `config/db_schema.php`

## Useful routes

```text
/
/home
/admin/login
/admin
/admin/pages
/admin/users
/admin/roles
/admin/themes
/admin/audit-log
/admin/login-history
/api/v1/health
/api/v1/content/page?slug=home
```

## Module templates

Modules can provide default views:

```text
app/zoosper-page/resources/views/page/view.php
```

Themes can override module views:

```text
themes/default/templates/modules/zoosper-page/page/view.php
```

Render using:

```text
zoosper-page::page/view
```

## Plug-and-play controllers

Modules can provide controller factories:

```text
app/<module>/config/controllers.php
```

This reduces the need to keep editing `ApplicationFactory` for every new controller.

## Documentation

Detailed architecture notes live in `docs/architecture/` and phase plans live in `docs/roadmap/`.
