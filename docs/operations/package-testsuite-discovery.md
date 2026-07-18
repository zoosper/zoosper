# Package testsuite discovery operations

Phase 1.37h.2 replaces the package-testsuite synchronisation tool with a parse-safe implementation.

Run:

```bash
php8.5 tools/ensure-package-testsuites.php --dry-run
php8.5 tools/ensure-package-testsuites.php
php8.5 tools/verify-package-testsuites.php
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

If the tool cannot locate a testsuite, add this line manually inside the root phpunit.xml testsuite:

```xml
<directory>packages/*/tests</directory>
```
