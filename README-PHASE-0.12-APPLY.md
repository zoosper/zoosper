# Apply Zoosper Phase 0.12 Schema Hardening Update

Apply from repository root:

```bash
unzip zoosper-phase-0.12-schema-hardening-update.zip -d /tmp/zoosper-phase-0.12
cp -R /tmp/zoosper-phase-0.12/zoosper-phase-0.12-schema-hardening-update/* .
chmod +x bin/zoosper-schema
composer dump-autoload
php bin/zoosper migrate
```

Set version if desired:

```env
CMS_VERSION=0.12.0-dev
```

Smoke test:

```bash
php -l app/zoosper-core/src/App/CmsVersion.php
php -l app/zoosper-page/src/Service/PageRenderer.php
php -l app/zoosper-core/src/Schema/SchemaValidationResult.php
php -l app/zoosper-core/src/Schema/SchemaValidator.php
php -l app/zoosper-core/src/Schema/SchemaSnapshotRepository.php
php -l app/zoosper-core/src/Schema/SchemaMigrator.php
php -l bin/zoosper-schema
```

Schema commands:

```bash
php bin/zoosper-schema validate
php bin/zoosper-schema diff
php bin/zoosper-schema apply
php bin/zoosper-schema snapshots
```

Browser test:

```text
/home
/admin/pages/preview?id=1
```

Expected: frontend footer should no longer say `Rendered by Zoosper Phase 0.3`; it should show the configured Zoosper CMS version.
