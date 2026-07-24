## Phase 1.53a-l: Page momentum source hook adapter

Status: ready to apply

Adds a passive source-level adapter and isolated adapter config for exposing the Page Momentum admin hook candidate to the real admin route/menu aggregation pipeline.

Safety:

- Existing aggregator files are not overwritten.
- Adapter is passive and read-only.
- Live mutation remains false.

Verification gates:

- `php8.5 tools/prove-page-admin-momentum-source-hook-adapter.php`
- `php8.5 tools/audit-page-admin-momentum-source-hook-readiness.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumSourceHookAdapterTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
