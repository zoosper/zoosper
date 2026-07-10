# Zoosper CMS

Zoosper is a modern, lightweight, modular PHP 8.5+ CMS inspired by Magento-style extensibility, Hyva-style frontend simplicity and Marko PHP module conventions.

## Current phase

Phase 0.23 — Admin Grid Pagination, Search and Filters.

## What is included

- Module-owned controller providers through `config/controllers.php`
- Module-owned admin/API routes, menus, ACL/resource config and views
- Module-owned log filenames through `config/logging.php`
- Admin form UI metadata through `config/admin_ui.php`
- Admin grid pagination/search/filter foundation
- Pages admin grid query service
- Frontend and admin theme foundations
- Layout updates with remove, replace and inject operations
- PCI-aware roadmap notes

## Pages grid filters

`/admin/pages` supports the foundation for:

```text
q
status
site_id
page
page_size
```

The controller integration remains module-owned in `zoosper-page`.

## Roadmap

Next planned phases include admin 2FA foundation and fuller form field injection implementation.
