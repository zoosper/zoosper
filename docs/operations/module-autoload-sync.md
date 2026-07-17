# Module autoload synchronisation

Run this after adding a new module with PHP classes:

```bash
php8.5 tools/sync-module-autoload.php
PHP=php8.5 composer dump-autoload
```

Verify:

```bash
php8.5 tools/verify-module-autoload-sync.php
PHP=php8.5 bin/verify
```

## Why this exists

Composer has no useful wildcard mapping for Zoosper's module folder layout. Instead of manually editing `composer.json` every time a module is added, Zoosper now generates explicit PSR-4 mappings from module metadata.

## Expected module metadata

```php
return [
    'name' => 'Vendor_Module',
    'enabled' => true,
];
```

The synchroniser ignores disabled modules and modules without a `src/` directory.
