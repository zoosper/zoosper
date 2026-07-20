# Vendor package module discovery

Phase 1.37q documents and audits how Zoosper should discover Composer-installed modules from `vendor/`.

## Blank USB principle

Zoosper core should stay as the blank USB. Installed Composer modules provide the content/capability identity:

```text
core + vendor/acme/movie-library = movie CMS
core + vendor/acme/health-data   = health CMS
core + vendor/acme/software      = software CMS
```

## Package contract

A Composer-installed Zoosper module should expose this shape:

```text
vendor/acme/movie-library/
  composer.json
  module.php
  config/
  src/
  tests/
```

Minimum `composer.json` metadata:

```json
{
  "name": "acme/movie-library",
  "type": "zoosper-module",
  "autoload": {
    "psr-4": {
      "Acme\\MovieLibrary\\": "src/"
    }
  },
  "extra": {
    "zoosper": {
      "module": "module.php",
      "name": "Acme_MovieLibrary"
    }
  }
}
```

Minimum `module.php`:

```php
<?php

declare(strict_types=1);

return [
    'name' => 'Acme_MovieLibrary',
    'enabled' => true,
    'version' => '0.1.0',
    'sort_order' => 100,
];
```

## Naming convention

```text
Composer package: acme/movie-library
Vendor path:      vendor/acme/movie-library
Zoosper module:   Acme_MovieLibrary
PHP namespace:    Acme\MovieLibrary\
```

## Discovery expectations

Zoosper should be able to discover module metadata from Composer's installed package metadata and then resolve module-owned config through the package directory:

```text
config/services.php
config/db_schema.php
config/admin_routes.php
config/api_routes.php
```

## Non-goals

This phase does not publish packages to Packagist, build a marketplace, or implement destructive uninstall behaviour. It audits and documents the package contract so future vendor-installed modules can be trusted safely.
