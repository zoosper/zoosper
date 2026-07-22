# Phase 1.37v.1 — Deferred Roadmap Visibility Hotfix

The bulk 1.37v roadmap update accidentally removed the `Deferred near-term` section from `docs/roadmap/roadmap-status.md`.

Those items should remain visible while the Launch Readiness Arc is active so they are not forgotten.

## Restored deferred items

```text
1.37n.5 — Optional media-gd/media-imagick processor package planning
1.38   — RoleAdminController Latte/template migration
1.39   — DB-backed rate limiting behind RateLimiterInterface
```

## Outcome

```text
- Restores `## Deferred near-term` to roadmap-status.md.
- Adds docs/roadmap/deferred-near-term.md as a durable parking file.
- Adds regression coverage so the deferred list is not silently dropped again.
```

## Verification

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Documentation/DeferredNearTermRoadmapTest.php
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```
