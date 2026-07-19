# Media schema migration CURRENT_TIMESTAMP hotfix operations

If `PHP=php8.5 bin/zoosper migrate` fails with:

```text
SQLSTATE[42000]: Syntax error or access violation: 1067 Invalid default value for 'created_at'
```

apply Phase 1.37m.3 and run:

```bash
vendor/bin/pest app/zoosper-core/tests/Unit/Schema/SchemaSqlBuilderTimestampDefaultTest.php
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
PHP=php8.5 bin/zoosper migrate
php8.5 tools/diagnose-media-schema-runtime.php
```

Expected after migration:

```text
media_assets table: ok
Result: OK
```

Then repeat the browser upload smoke test from `/admin/pages/create`.
