# Zoosper CMS

Zoosper is a modern, lightweight, modular PHP 8.5+ CMS inspired by Magento-style extensibility, Hyva-style frontend simplicity and Marko PHP module conventions.

## Current phase

Phase 0.22 — Admin Form Field Injection.

## What is included

- Module-owned controller providers through `config/controllers.php`
- Module-owned admin/API routes, menus, ACL/resource config and views
- Module-owned log filenames through `config/logging.php`
- Admin form UI metadata through `config/admin_ui.php`
- Field remove, replace and inject foundations
- Frontend and admin theme foundations
- Layout updates with remove, replace and inject operations
- Permission tree groups sorted alphabetically by parent label
- Local error handling and local log files
- Declarative schema engine using module `config/db_schema.php`

## Admin form UI metadata

Modules can define form fields in:

```text
app/<module>/config/admin_ui.php
```

Supported operations:

```text
fields
remove
replace
inject
```

## Module-owned logging

Modules can define their own log filenames in:

```text
app/<module>/config/logging.php
```

This keeps `ApplicationFactory` marketplace-module friendly.
