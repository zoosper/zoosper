# Module development and overrides

## Where to place custom modules

Use `modules/`, not `app/`, for third-party, community, marketplace or project-specific modules.

Recommended structure:

```text
modules/acme/custom-feature/
  module.php
  config/
    services.php
    controllers.php
    admin_routes.php
    admin_menu.php
    db_schema.php
  src/
  resources/views/
```

## module.php example

```php
return [
    'name' => 'acme-custom-feature',
    'enabled' => true,
    'sort_order' => 900,
];
```

Use a higher `sort_order` than core modules when intentionally overriding service IDs.

## Service override example

```php
return [
    VendorInterface::class => static fn (ServiceContainer $services): VendorInterface => new CustomVendorService(),
];
```

## Rules

- Do not edit core modules for custom projects.
- Do not inject the service container into business services.
- Use constructor injection and module-owned services.php factories.
- Never store credentials, OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment data or customer-private values in module metadata or service IDs.
