# Zoosper CMS

Zoosper is a modern, lightweight, modular PHP 8.5+ CMS inspired by Magento-style extensibility, Hyva-style frontend simplicity and Marko PHP module conventions.

## Current phase

Phase 0.17 — Admin Components and Module UI.

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
- Declarative schema engine using module `config/db_schema.php`

## Layout updates

Themes can hide, replace or inject templates through `layout.php`:

```php
return [
    'admin.layout' => [
        'remove' => ['partials/footer.php'],
        'replace' => ['partials/header.php' => 'partials/custom-header.php'],
        'inject' => ['before.content' => ['partials/notice.php']],
    ],
];
```

## Module UI

Modules can own their views:

```text
app/zoosper-admin/resources/views/dashboard/index.php
```

Themes can override module views:

```text
themes/admin/default/templates/modules/zoosper-admin/dashboard/index.php
```

## Documentation

Detailed architecture notes live in `docs/architecture/` and phase plans live in `docs/roadmap/`.
