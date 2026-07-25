# Site Lookup Arc — Consolidation Status

This note records the completed site-lookup tooling arc so the work is durable
and nothing is forgotten in future planning.

## Timeline

| Phase | Outcome |
| --- | --- |
| 1.75 | Boundary drift guard introduced (initial, over-strict). |
| 1.75 hotfix V2 | Guard tuned: hard errors only for page hot-path `SiteResolver`; broad concrete `SiteRepository` references reclassified as migration candidates. |
| 1.76 | Migration candidate tracker + assisted, dry-run-first migration helper. |
| 1.77 | Three standalone scripts consolidated into one CLI (`tools/site-lookup.php`); legacy scripts retired. |
| 1.78 | Audit wired into the quality gate runner (`tools/gate.php`) for automatic protection. |

## Current entry points

- `php8.5 tools/site-lookup.php audit` — boundary drift guard.
- `php8.5 tools/site-lookup.php snapshot` — migration candidate tracker.
- `php8.5 tools/site-lookup.php migrate [--apply]` — assisted migration.
- `php8.5 tools/gate.php` — runs the audit (and future checks) as a fail-fast gate.

## Outstanding (deliberately deferred)

- Migration candidates listed in `docs/development/site-lookup-migration-candidates.md`
  remain informational. Progress them in a dedicated cleanup phase when convenient;
  they do not block the gate.

## Status

The site-lookup boundary arc is **complete and protected**. No further structural
work is required unless the migration candidate backlog is prioritised.
