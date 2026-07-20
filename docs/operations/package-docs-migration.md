# Package docs migration operations

Phase 1.37t.1 fixes the media package docs policy test path. From:

```text
packages/zoosper-media/tests/Unit/Documentation
```

three levels up resolves to:

```text
packages/zoosper-media
```

Run targeted tests:

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Documentation/PackageOwnedDocumentationPolicyTest.php packages/zoosper-media/tests/Unit/Documentation/MediaPackageDocsPolicyTest.php
```

Run the package docs audit:

```bash
php8.5 tools/audit-doc-package-ownership.php
```

Generate migration candidates if needed:

```bash
php8.5 tools/plan-package-docs-migration.php
```

The generated plan is an inspection artefact. Do not commit it unless intentionally promoted.

Then run full verification:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```
