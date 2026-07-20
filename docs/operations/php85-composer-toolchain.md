# PHP 8.5 Composer toolchain

Zoosper requires PHP 8.5+. On machines where the default `php` command still points to PHP 8.3, running Composer through the wrong PHP binary can trigger Composer platform-check failures.

## Correct commands

Use this when running Composer manually:

```bash
php8.5 $(which composer) dump-autoload
```

Or use the wrapper added in this phase:

```bash
tools/composer-php85.sh dump-autoload
```

Run Pest explicitly with PHP 8.5:

```bash
php8.5 vendor/bin/pest
```

The existing verification wrapper remains the preferred all-in-one check:

```bash
PHP=php8.5 bin/verify
```

## Avoid this form

Avoid:

```bash
PHP=php8.5 composer dump-autoload
```

That sets an environment variable named `PHP`, but it does not necessarily make the `composer` executable itself run under PHP 8.5. If Composer is launched by PHP 8.3, Composer's internal `@php` scripts may still execute under PHP 8.3 and fail the platform check.

## Why this matters

Composer scripts such as:

```text
@php tools/sync-module-autoload.php
```

use the PHP runtime Composer is currently using. Therefore Composer itself must be launched by PHP 8.5.
