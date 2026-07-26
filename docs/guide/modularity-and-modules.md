# Modularity & modules

Zoosper’s bet: **extend anything without forking core**. A module is a folder with `module.php` and convention-based `config/*.php` files. Drop it in; delete it; core stays untouched.

## Mental model

```text
app/acme-blog/   (or modules/acme/blog/)
├── module.php
├── src/                    PSR-4 PHP
├── config/
│   ├── services.php        DI factories (can override core services)
│   ├── controllers.php     Controller factories
│   ├── admin_routes.php    Admin HTTP routes
│   ├── api_routes.php      JSON API routes
│   ├── db_schema.php       Tables and columns
│   ├── admin_menu.php      Admin navigation
│   ├── acl.php             Permissions
│   ├── admin_forms.php     Form sections and processors
│   ├── admin_ui.php        Add/replace/remove/inject form fields
│   ├── entity_save_listeners.php
│   ├── events.php          General event subscribers
│   ├── logging.php         Module log file
│   └── settings/*.php      Default config (overridden by root config/)
├── i18n/{locale}.php
└── resources/views/        Latte/PHP views (theme-overridable)
```

## What modules own today

| Concern | Config / mechanism |
|---------|-------------------|
| Routes | `admin_routes.php`, `api_routes.php` |
| Controllers | `controllers.php` |
| Services / DI | `services.php` (later module can replace a binding) |
| Database | `db_schema.php` → unified schema engine |
| Admin menu & ACL | `admin_menu.php`, `acl.php` |
| Admin forms | `admin_forms.php`, `admin_ui.php` |
| Save hooks | `entity_save_listeners.php` |
| Side-effect hooks | `events.php` |
| Logging | `logging.php` |
| Views | `resources/views` + `module::path` overrides |
| Translations | `i18n/` |
| Config defaults | `config/settings/*.php` merged under root `config/` |

**Not yet available:** method plugins/interceptors (Magento-style around/before/after on arbitrary methods). Service **replacement** via DI is supported today.

## module.php

```php
<?php

declare(strict_types=1);

return [
    'name' => 'acme-blog',
    'enabled' => true,
    'sort_order' => 900,  // higher = registered later (useful for overrides)
];
```

Use a higher `sort_order` when intentionally overriding another module’s service ID.

## Custom module placement

Put third-party and project code under `modules/`, not `app/`:

```text
modules/acme/blog/module.php
modules/acme/blog/config/services.php
```

See [Composer & marketplace modules](composer-and-marketplace-modules.md) for vendor-installed modules.

## Service override example

```php
<?php

declare(strict_types=1);

use Zoosper\Core\Container\ServiceContainer;

return [
    SomeInterface::class => static fn (ServiceContainer $services): SomeInterface =>
        new CustomImplementation(/* injected deps */),
];
```

## Scaffold a new module

```bash
php bin/zoosper make:module Acme_Blog
```

Add the PSR-4 autoload snippet from the generated README, then `composer dump-autoload`.

Details: [Module generator](../contributor/module-generator.md).

## Coding rules (summary)

- Thin controllers; business logic in services; SQL in repositories.
- `declare(strict_types=1);` in every PHP file.
- Constructor injection only — no service locator inside domain code.
- Parameterised SQL only; escape admin/frontend output by default.
- Never store credentials, OTPs, or payment data in module metadata.

Full agent rules: [AGENTS.md](../../AGENTS.md).

## Related guides

- [Routing, middleware & access control](routing-middleware-and-access-control.md)
- [Entity save lifecycle](entity-save-lifecycle.md)
- [Schema & database](schema-and-database.md)
