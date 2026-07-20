# Phase 1.37s.1 - PHP 8.5 Composer guidance cleanup

## Goal

Remove the need for a committed shell wrapper while preserving the PHP 8.5 Composer workflow guidance.

## Diagnosis

`tools/composer-php85.sh` worked when run through `bash`, but it appeared in the tools inventory REVIEW bucket. Since the workflow is simple and already proven, the cleaner project-hygiene choice is to document the canonical command rather than keep a new shell wrapper.

## Implemented

- Updated the PHP 8.5 Composer toolchain test to validate documentation instead of requiring a shell wrapper.
- Updated the operations documentation to use the direct command:

```bash
php8.5 $(which composer) dump-autoload
```

- Documented why `PHP=php8.5 composer dump-autoload` is not reliable when the Composer process itself is launched by PHP 8.3.

## Manual cleanup

Remove the wrapper if it exists locally:

```bash
rm -f tools/composer-php85.sh
```

Then run:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```

## Expected result

Tools inventory should return to REVIEW 0.
