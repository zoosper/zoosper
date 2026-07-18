# Package testsuite normalisation operations

This hotfix replaces the normalisation tool with a comment-free parse-safe implementation.

Run:

```bash
php8.5 tools/normalise-package-testsuites.php --dry-run
php8.5 tools/normalise-package-testsuites.php
php8.5 tools/verify-package-testsuites.php
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

If needed, the equivalent manual replacement is:

```bash
perl -0pi -e 's#<directory>packages/\*/tests</directory>#<directory>packages/*/tests/Unit</directory>#' phpunit.xml
```
