# Package-aware module scaffolding operations

Phase 1.37p.4 fixes the generated package test filename. The file map key previously used single quotes:

```php
'tests/Unit/{$classPrefix}PackageTest.php'
```

That created a literal file named:

```text
{$classPrefix}PackageTest.php
```

The key now uses interpolation and creates:

```text
MovieLibraryPackageTest.php
```

Run targeted tests:

```bash
vendor/bin/pest app/zoosper-core/tests/Unit/Scaffold/PackageModuleScaffolderTest.php app/zoosper-core/tests/Unit/Scaffold/PackageModuleScaffolderRegexTest.php app/zoosper-core/tests/Unit/Scaffold/PackageModuleScaffolderNamingTest.php app/zoosper-core/tests/Unit/Scaffold/PackageModuleScaffolderModuleNameTest.php app/zoosper-core/tests/Unit/Scaffold/PackageModuleScaffolderGeneratedFilenameTest.php
```

Then run full verification:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```
