# Clean Modular Migrator

The clean long-term solution is to make `bin/zoosper migrate` responsible for both traditional migration files and enabled module-owned declarative schema files.

## Behaviour

`Zoosper\Core\Database\Migrator` now:

1. runs pending PHP migration files from `database/migrations`
2. supports several migration payload styles for backwards compatibility
3. tracks applied files in `migrations`
4. applies every enabled module's `config/db_schema.php` through `DeclarativeSchemaApplier`

## Module marketplace friendliness

A marketplace module only needs to include:

```text
app/vendor-module/config/db_schema.php
```

The module is discovered by `ModuleRegistry`, and its schema is applied by `bin/zoosper migrate` without editing core bootstrap files.

## Safety

The declarative schema applier is additive. It creates missing tables and indexes, but does not drop or alter existing columns. Destructive changes should be explicit audited migrations.

## PCI-aware note

2FA tables store protected secret payloads and recovery-code hashes only. OTPs, plaintext recovery codes and TOTP secrets must never be logged.
