# Zoosper CMS

Zoosper is a modern, lightweight, modular PHP 8.5+ CMS inspired by Magento-style extensibility, Hyva-style frontend simplicity and Marko PHP module conventions.

## Current phase

Phase 0.20 — Controller Thinning and Form Components.

## What is included

- Modular application structure under `app/` and `modules/`
- Module-owned controller providers through `config/controllers.php`
- Module-owned admin/API routes, menus, ACL/resource config and views
- Frontend and admin theme foundations
- Admin component templates and reusable form field components
- Layout updates with remove, replace and inject operations
- Per-site `theme_code`
- Admin users, roles, permission tree, audit log and login history
- Local logging foundation with module-specific log filenames
- Declarative schema engine using module `config/db_schema.php`

## Local logging foundation

Configure local log files in:

```text
config/logging.php
```

Module log files can be named individually, for example:

```php
'zoosper-theme' => 'theme.log'
```

## Development principle

Controllers should prepare data and delegate rendering to module-owned views/components. Routes, APIs, ACLs, menus, schemas and controllers should remain inside their respective modules so modules can be added or removed more easily.
