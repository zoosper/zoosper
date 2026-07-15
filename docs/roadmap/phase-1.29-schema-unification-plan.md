# Phase 1.29 Plan - Schema Engine Unification (Module-Owned Schema)

## Goal

Unify Zoosper's **two** database schema engines into **one**. Keep the
snapshot-capable `Schema/` engine, retire `DeclarativeSchemaApplier`, normalise
every module's `config/db_schema.php` to a single format, fold the stray
`database/schema/*.php` column additions into their owning modules, and make
database schema **truly module-owned**.

---

## Plain-English primer

- **Schema engine** - the code that turns each module's *description of its
  tables* into real `CREATE TABLE` / `ALTER TABLE` SQL.
- **Declarative** - you describe the *end state you want* (this table, these
  columns), and the engine works out the SQL needed to get there. You do not
  hand-write `CREATE TABLE`.
- **Snapshot** - an audit record of every schema change the engine applied (a
  hash + the exact statements + a timestamp), so you can see the history of how
  the database structure evolved.

---

## The critical finding: two engines, incompatible formats

There are currently **two separate code paths** that both read module
`config/db_schema.php` files - in **incompatible** ways.

| | **Engine A - `Schema/`** (keep) | **Engine B - `DeclarativeSchemaApplier`** (retire) |
|---|---|---|
| Pipeline | `SchemaLoader` -> `SchemaRegistry` -> `SchemaValidator` -> `SchemaMigrator` -> `SchemaSqlBuilder` + `SchemaInspector` -> `SchemaSnapshotRepository` | `DeclarativeSchemaApplier::applyAll()` |
| Entry point | `bin/zoosper-schema` (validate/diff/apply/snapshots) | `bin/zoosper migrate` (via `Migrator::applyModuleSchemas`) |
| Validation | ✅ `SchemaValidator` | ❌ none |
| Audit snapshots | ✅ yes | ❌ no |
| ALTER / add-column to existing table | ✅ yes | ❌ create-only |
| Column types | integer, int, bigint, string, text, datetime, **boolean, json** | integer, string, text, datetime **only** |
| **Expected file format** | top-level keys **are** table names | requires a **`'tables' =>`** wrapper |

### The format inconsistency (the latent bug)

The two engines disagree on the **shape** of `db_schema.php`, and your modules
are split across both shapes:

```php
// zoosper-core, zoosper-site  -> NO wrapper (Engine A's expected shape)
return ['schema_snapshots' => ['columns' => [...]]];

// zoosper-page, zoosper-two-factor  -> WITH 'tables' wrapper (Engine B's shape)
return ['tables' => ['page_site_assignments' => ['columns' => [...]]]];
```

**Consequences today:**

- `bin/zoosper-schema validate` would **error** on `zoosper-page` /
  `zoosper-two-factor` (it reads the literal key `tables` as a table name that
  has no columns).
- `DeclarativeSchemaApplier` (run by `bin/zoosper migrate`) **silently ignores**
  `zoosper-core` / `zoosper-site` (no `tables` key -> nothing to do).

So each half of your schema only works through a *different* command. This is
exactly the drift the review docs warned about.

**A third shape** also exists: `database/schema/*.php`
(`admin_user_locale`, `page_seo_metadata`, `page_content_format`) uses
`['table' => 'pages', 'columns' => [... 'definition'|type/length, 'after',
'comment', 'safe_defaults']]` to add columns to **existing** tables. These belong
in the owning module's `db_schema.php`.

---

## Decision

- **Keep Engine A** (`Schema/`): it is validated, audited (snapshots), supports
  `ALTER`/add-column, and handles all needed types (boolean, json...).
- **Retire Engine B** (`DeclarativeSchemaApplier`).
- **Standardise on the `['tables' => [...]]` wrapper** - it is clearer and
  future-proofs the file for other top-level keys later (e.g. `seeds`,
  `foreign_keys`).
- **Update `SchemaLoader`** to read `$config['tables']`, with a **descriptive
  `ZoosperException`** if a module still uses the old flat format (telling the
  author exactly how to wrap it).
- **Convert** `zoosper-core` + `zoosper-site` `db_schema.php` to the wrapper.
- **Fold** the `database/schema/*.php` column-adds into their owning modules'
  `db_schema.php` as columns on the existing table (the `SchemaMigrator` already
  supports adding columns to existing tables).

---

## Pros / cons

| | Unify now (recommended) | Leave as-is |
|---|---|---|
| **Pros** | One command, one format; fixes the silent-ignore + validate-error bugs; true module-owned schema; audit snapshots for *all* schema | No work now |
| **Cons** | Touches `database/` + a few module schema files (mitigated: conversion is mechanical and gated by `bin/zoosper-schema validate` + Pest) | The two-engine drift and latent bugs remain; not "true modular" |

---

## Scope IN

1. Update `SchemaLoader` to read the `['tables' => [...]]` wrapper, throwing a
   descriptive `ZoosperException` for the old flat format.
2. Convert `zoosper-core` + `zoosper-site` `db_schema.php` to the wrapper.
3. Migrate the 3 `database/schema/*.php` column files into their owning modules:
   - `admin_user_locale` -> `app/zoosper-auth/config/db_schema.php` (locale column on `admin_users`)
   - `page_seo_metadata` + `page_content_format` -> `app/zoosper-page/config/db_schema.php` (columns on `pages`)
4. Make `bin/zoosper migrate` delegate module schema to `SchemaMigrator` (the
   `Schema/` engine) instead of `DeclarativeSchemaApplier`, so **one** engine runs
   everywhere.
5. Remove `DeclarativeSchemaApplier` and the
   `20260710002600_apply_module_declarative_schemas` migration once superseded.
6. Add Pest tests (co-located in `zoosper-core`) for `SchemaLoader` (wrapper +
   descriptive error), `SchemaValidator`, `SchemaSqlBuilder` (create / add-column /
   index for mysql + sqlite), and `SchemaMigrator::diff()`.

---

## Scope OUT (guardrails)

- Do **not** convert the existing base-table migrations in
  `database/migrations/` - they already ran; leave history intact.
- No destructive drops/renames - the engine stays **additive** (create table,
  add missing column, add missing index only).
- No data migrations.
- Keep all `bin/zoosper-schema` commands working.
- Respect the MySQL-first policy (`config/database_policy.php`).

---

## Risks & mitigations

| Risk | Mitigation |
|---|---|
| Format conversion breaks a module | Gate every step behind `bin/zoosper-schema validate`; Pest tests for the loader/validator/builder |
| Both engines create overlapping objects during transition | Engine is idempotent (`CREATE TABLE IF NOT EXISTS`, add-column-if-missing) - safe to run either path |
| `schema_snapshots` table is itself declared in core schema | Ensure it is created before snapshotting (bootstrap ordering) - note for Step 5 |
| SQLite vs MySQL divergence | `SchemaSqlBuilder` already branches on driver; tests cover both |

---

## Acceptance criteria

- A **single** engine (`Schema/`) applies **all** module schema via **both**
  `bin/zoosper-schema apply` and `bin/zoosper migrate`.
- `bin/zoosper-schema validate` passes for **every** module.
- `DeclarativeSchemaApplier` removed; `database/schema/*.php` folded into modules.
- Audit snapshots recorded on apply.
- Pest green (adds ~6-8 tests).
- `MIGRATE_TO_PEST` drops as schema `verify-*` scripts retire.

---

## Sequenced steps (each verified before the next)

1. **Tests first** - lock in current `SchemaLoader` / `SchemaValidator` /
   `SchemaSqlBuilder` behaviour with Pest.
2. Add `['tables' => ...]` wrapper support to `SchemaLoader` + a descriptive
   error for the old flat format.
3. Convert `zoosper-core` + `zoosper-site` schema files to the wrapper.
4. Fold `database/schema/*.php` column files into their owning modules.
5. Point `Migrator` at `SchemaMigrator` (retire `DeclarativeSchemaApplier` usage).
6. Remove `DeclarativeSchemaApplier` + the old apply migration.
7. Docs + retire the schema `verify-*` scripts.

Each step: run `bin/zoosper-schema validate`, `bin/zoosper migrate` against a
scratch DB, and `./vendor/bin/pest`.

---

## What I still need before coding Step 2+

I already have `SchemaLoader`, `SchemaMigrator`, `SchemaSqlBuilder`,
`SchemaValidator`, `SchemaInspector`, `SchemaSnapshotRepository`, `SchemaRegistry`,
`SchemaTable`, `Migrator`, `DeclarativeSchemaApplier`, `bin/zoosper-schema`, and
the `db_schema.php` for core/page/site/two-factor plus the `database/schema/*.php`
files.

Please paste (inline) the **3 remaining** module schema files so the conversion
covers every module:

- `app/zoosper-admin/config/db_schema.php`
- `app/zoosper-mail/config/db_schema.php`
- `app/zoosper-url-rewrite/config/db_schema.php`
