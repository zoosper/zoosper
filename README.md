# Zoosper CMS

Zoosper is a modern, lightweight, modular PHP 8.5+ CMS inspired by Magento-style extensibility, Hyva-style frontend simplicity and Marko PHP module conventions.

## Current phase

Phase 0.21 — Local Error Handling and Module Logging.

## What is included

- Modular application structure under `app/` and `modules/`
- Module-owned controller providers through `config/controllers.php`
- Module-owned admin/API routes, menus, ACL/resource config and views
- Frontend and admin theme foundations
- Admin component templates and reusable form field components
- Layout updates with remove, replace and inject operations
- Per-site `theme_code`
- Local error handling and local log files
- Module-specific log filenames under `var/log`
- Declarative schema engine using module `config/db_schema.php`

## Logging

Logging is configured in:

```text
config/logging.php
```

Default logs are written to:

```text
var/log/system.log
var/log/exception.log
var/log/theme.log
var/log/page.log
var/log/admin.log
```

## Development principle

Controllers should prepare data and delegate rendering to module-owned views/components. Routes, APIs, ACLs, menus, schemas and controllers should remain inside their respective modules so modules can be added or removed more easily.
