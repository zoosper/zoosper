# Latte template engine setup

## Composer dependency

Install Latte with Composer:

```bash
composer require latte/latte:^3.1
composer dump-autoload
```

## Verify

```bash
php tools/verify-latte-template-engine.php
php tools/diagnose-latte-template-engine.php
php tools/verify-service-providers.php
```

## Cache directory

Latte compiles templates into the configured cache path:

```text
var/cache/templates
```

The PHP user must be able to create and write to this directory.

## Template extension order

The registry supports:

```text
.latte
.php
```

Existing PHP templates still work. New templates can be written as `.latte`.
