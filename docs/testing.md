# Testing and quality gates

Run focused Pest tests during development and the full suite before committing.

```bash
composer test
composer compile
composer fresh-install:smoke
composer release:check
composer gate:strict
```

The fresh-install smoke test uses disposable SQLite state, applies migrations twice, creates an Admin and Site, checks password hashing and validates critical routes/assets. The quick-start environment contract also guards local HTTP session compatibility, disabled-by-default throttling, and the built-in server front-controller router.

The strict gate validates durable repository contracts. Psalm remains visible and advisory while the inherited baseline is reduced; new code should not intentionally add static-analysis debt.
