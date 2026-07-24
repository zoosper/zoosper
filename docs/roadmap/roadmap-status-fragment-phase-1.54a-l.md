## Phase 1.54a-l: Page momentum runtime aggregation candidate

Status: ready to apply

Adds a runtime-facing provider and isolated config for exposing the Page Momentum admin route/menu payload to the real admin aggregation pipeline.

Safety:

- Existing aggregator files are not overwritten.
- Provider is passive and read-only.
- Live mutation remains false.

Verification gates:

- `php8.5 tools/prove-page-admin-momentum-runtime-aggregation-candidate.php`
- `php8.5 tools/audit-page-admin-momentum-runtime-aggregation-readiness.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumRuntimeAggregationCandidateTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
