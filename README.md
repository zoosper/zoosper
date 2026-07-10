# Zoosper CMS

Zoosper is a modern, lightweight, modular PHP 8.5+ CMS inspired by Magento-style extensibility, Hyva-style frontend simplicity and Marko PHP module conventions.

## Current phase

Phase 0.19 — Pages, Users and Roles View Refactor.

## What is included

- Modular application structure under `app/` and `modules/`
- Module-owned controller providers through `config/controllers.php`
- Module-owned admin/API routes through route config files
- Module-owned admin menus and ACL/resource config foundations
- Module template rendering using `module::path`
- Frontend and admin theme foundations
- Admin component templates under `themes/admin/default/templates/components`
- Module-owned admin views for pages, users and roles
- Layout updates with remove, replace and inject operations
- Per-site `theme_code`
- Admin users, roles, permission tree, audit log and login history
- Declarative schema engine using module `config/db_schema.php`

## Admin module views

Pages:

```text
app/zoosper-page/resources/views/admin/pages/index.php
app/zoosper-page/resources/views/admin/pages/form.php
```

Users and roles:

```text
app/zoosper-auth/resources/views/admin/users/index.php
app/zoosper-auth/resources/views/admin/users/form.php
app/zoosper-auth/resources/views/admin/roles/index.php
app/zoosper-auth/resources/views/admin/roles/form.php
```

## Development principle

Controllers should prepare data and delegate rendering to module-owned views. Routes, APIs, ACLs, menus, schemas and controllers should remain inside their respective modules so modules can be added or removed more easily.
