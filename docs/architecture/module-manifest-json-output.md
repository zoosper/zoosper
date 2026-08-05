# Module manifest JSON output

Phase 8G adds opt-in machine-readable output to the existing manifest status and health-check commands.

```bash
php8.5 bin/zoosper module:manifest:status --format=json
php8.5 bin/zoosper module:manifest:check --format=json
```

Text remains the default for operators. JSON status output preserves the Phase 8E fields. JSON check output adds `healthy` while retaining Phase 8F exit semantics: `0` for fresh and `1` for missing or rejected.
