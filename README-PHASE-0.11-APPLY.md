# Apply Zoosper Phase 0.11 Declarative Schema Update

Apply from repository root:

```bash
unzip zoosper-phase-0.11-declarative-schema-update.zip -d /tmp/zoosper-phase-0.11
cp -R /tmp/zoosper-phase-0.11/zoosper-phase-0.11-declarative-schema-update/* .
chmod +x bin/zoosper-schema
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l app/zoosper-core/src/Schema/SchemaTable.php
php -l app/zoosper-core/src/Schema/SchemaRegistry.php
php -l app/zoosper-core/src/Schema/SchemaLoader.php
php -l app/zoosper-core/src/Schema/SchemaInspector.php
php -l app/zoosper-core/src/Schema/SchemaSqlBuilder.php
php -l app/zoosper-core/src/Schema/SchemaMigrator.php
php -l app/zoosper-core/config/db_schema.php
php -l app/zoosper-admin/config/db_schema.php
php -l bin/zoosper-schema
```

Run diff:

```bash
php bin/zoosper-schema diff
```

Apply safe additive schema updates:

```bash
php bin/zoosper-schema apply
```

Expected first apply may create `schema_snapshots`; existing audit tables should usually be detected as already present.
