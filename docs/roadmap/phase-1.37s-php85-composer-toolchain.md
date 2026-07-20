# Phase 1.37s - PHP 8.5 Composer toolchain hardening

## Goal

Prevent repeated false failures caused by running Composer under PHP 8.3 while the project requires PHP 8.5+.

## Implemented

- Added `tools/composer-php85.sh` as a small wrapper that executes Composer through PHP 8.5.
- Added tests locking the wrapper behaviour and documentation guidance.
- Added operations documentation explaining why `PHP=php8.5 composer dump-autoload` is not sufficient on machines where Composer itself is launched by PHP 8.3.

## Recommended commands

```bash
php8.5 $(which composer) dump-autoload
php8.5 vendor/bin/pest
PHP=php8.5 bin/verify
```

or:

```bash
tools/composer-php85.sh dump-autoload
```

## Next phase

Continue with Phase 1.37r.3: migrate `MediaAdminController::upload()` to `MediaUploadService` after using the inspection output to preserve existing admin response behaviour.
