# Zoosper CMS

Zoosper is a modern, lightweight, modular PHP 8.5+ CMS inspired by Magento-style extensibility, Hyva-style frontend simplicity and Marko PHP module conventions.

## Current phase

Phase 0.18 — Admin Controller View Refactor.

## What is included

- Modular application structure under `app/` and `modules/`
- Module-owned controller providers through `config/controllers.php`
- Module-owned admin/API routes through route config files
- Module-owned admin menus and ACL/resource config foundations
- Module template rendering using `module::path`
- Frontend and admin theme foundations
- Admin component templates under `themes/admin/default/templates/components`
- Layout updates with remove, replace and inject operations
- Per-site `theme_code`
- Admin users, roles, permission tree, audit log and login history
- Refactored dashboard, audit log, login history and theme admin screens using module-owned views
- Declarative schema engine using module `config/db_schema.php`

## Admin views

Admin controllers should prepare data and render module-owned views:

```text
app/<module>/resources/views/...
```

Admin themes can override those views:

```text
themes/admin/default/templates/modules/<module>/...
```

## Documentation

Detailed architecture notes live in `docs/architecture/` and phase plans live in `docs/roadmap/`.
