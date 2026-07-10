# Auto Schema Not Created by `bin/zoosper migrate`

## Current known diagnostic issue fixed in v2

The first diagnostic script failed because `config/database.php` calls `env()` and the standalone script was not loading the normal bootstrap before loading configuration. The v2 script defines a minimal `env()` helper before calling `ConfigRepository::fromPath()`.

## Run

```bash
php tools/diagnose-migration-discovery.php
```

## Interpretation

- If schema files appear under the direct filesystem scan but not under discoverable modules, fix `ModuleRegistry`.
- If schema files appear under discoverable modules but tables are still missing, fix the declarative schema migrator or `bin/zoosper migrate` command.
- If no schema files appear, confirm the Phase 0.25 files were copied into `app/zoosper-url-rewrite/config/db_schema.php` and `app/zoosper-two-factor/config/db_schema.php`.

## Latest files needed for the full replacement fix

If the v2 script shows that the schema files are discovered but tables are missing, provide the latest migration/bootstrap files so the migrator can be replaced safely with full code:

```text
bin/zoosper
app/zoosper-core/src/Module/ModuleRegistry.php
app/zoosper-core/src/Database/**
app/zoosper-core/src/Console/**
app/zoosper-install/src/**
```
