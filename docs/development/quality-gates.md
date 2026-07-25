# Zoosper Quality Gates

## Purpose

The quality gate runner (`tools/gate.php`) is a single entry point that runs
registered guard checks and fails the build if any report hard errors. It exists
so architectural protections run automatically instead of relying on someone
remembering to run each audit by hand.

## Running

```bash
php8.5 tools/gate.php
echo "exit code: $?"
```

- Exit code `0` — all checks passed.
- Exit code `1` — one or more checks reported hard errors.

## Registered checks

| Check | Source | Fails on |
| --- | --- | --- |
| `site-lookup:audit` | `tools/site-lookup.php audit` | Page hot-path `SiteResolver` regressions |

Migration candidates and warnings are informational and do **not** fail the gate.

## Adding a new check

1. Open `tools/gate.php`.
2. Write a callable returning `['name' => string, 'errors' => int, 'summary' => string]`.
3. Append it to the `$checks` array.

No new files are needed per gate — this keeps the `tools/` directory lean.

## CI / hook integration

- CI: run `php8.5 tools/gate.php` as a build step; a non-zero exit fails the job.
- Local: optionally add a composer script `"gate": "php8.5 tools/gate.php"` and
  call it from a pre-push hook.
