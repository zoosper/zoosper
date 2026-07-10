# Migration Auto-run Policy

Database changes should be defined in module-owned `config/db_schema.php` files whenever possible.

## Rule

`bin/zoosper migrate` should be the only required command for local schema updates.

## Why url_rewrites did not auto-create before

The previous Phase 0.24 package included a raw SQL migration file. The current migration runner is intended to use module-owned declarative schema definitions, so this package adds:

```text
app/zoosper-url-rewrite/config/db_schema.php
```

That allows `url_rewrites` to be created automatically by the normal migration command, assuming the current declarative schema runner is enabled.
