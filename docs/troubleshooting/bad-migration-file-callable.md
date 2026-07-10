# Bad Migration File: Callable Bridge Fix

## Symptom

`php bin/zoosper migrate` fails with:

```text
RuntimeException: Bad migration file: database/migrations/20260710002600_apply_module_declarative_schemas.php
```

## Cause

The core `Migrator` expects each PHP migration file to return a callable. The previous bridge migration executed immediately and returned nothing, so the migrator rejected it as a bad migration file.

## Fix

The replacement migration file now returns:

```php
return static function (\PDO $pdo): void {
    // apply module-owned config/db_schema.php files
};
```

This matches the expected migration contract and keeps `bin/zoosper migrate` as the single schema update command.
