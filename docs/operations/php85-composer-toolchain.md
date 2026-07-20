# PHP 8.5 Composer toolchain

Zoosper requires PHP 8.5+. On machines where the default `php` command still points to PHP 8.3, running Composer through the wrong PHP binary can trigger Composer platform-check failures.

## Correct commands

Run Composer itself through PHP 8.5:

```bash
php8.5 $(which composer) dump-autoload
```

Run Pest explicitly with PHP 8.5:

```bash
php8.5 vendor/bin/pest
```

Use the existing verification wrapper for the full suite:

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

## Repo hygiene note

Do not keep one-off shell wrappers for this workflow unless they are classified as durable ops tooling. The canonical command is the direct Composer invocation above:

```bash
php8.5 $(which composer) dump-autoload
```
