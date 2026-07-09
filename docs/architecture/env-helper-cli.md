# CLI env() Helper Fix

Phase 0.12 introduced `config/app.php` using `env()`. The web/bootstrap path may define that helper, but `bin/zoosper-schema` loads config directly and therefore hit:

```text
PHP Fatal error: Call to undefined function env()
```

Phase 0.13 fixes this in two ways:

1. `config/app.php` no longer depends on a global `env()` helper.
2. `bin/zoosper-schema` defines a small local `env()` helper before config files are loaded, so other config files that still use `env()` are safe.
```
