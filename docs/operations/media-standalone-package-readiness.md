# Media standalone package readiness operations

Run targeted package readiness checks:

```bash
php8.5 tools/audit-media-standalone-package.php
vendor/bin/pest app/zoosper-core/tests/Unit/Composer/MediaStandalonePackageReadinessTest.php packages/zoosper-media/tests/Unit/Composer/PackageMetadataTest.php
```

If `PackageMetadataTest` fails with `null is identical to 'zoosper/media'`, check that the package-local test resolves the package root with:

```php
$packageRoot = dirname(__DIR__, 3);
```

From `packages/zoosper-media/tests/Unit/Composer`, three levels up is `packages/zoosper-media`.

Run full project verification:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

## Standalone repository extraction checklist

When creating the future `zoosper/media` repository, copy these paths from `packages/zoosper-media`:

```text
composer.json
module.php
config/
src/
tests/
phpunit.xml.dist
README.md
.gitignore
.github/workflows/tests.yml
```

Do not copy root runtime uploads:

```text
public/media/
storage/media/
```

Those are project runtime data, not package source.

## Important limitation

The package is repository-ready, but not fully independently installable until the dependent first-party packages such as `zoosper/core`, `zoosper/admin` and `zoosper/auth` are also published or provided through path repositories.
