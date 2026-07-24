# Phase 1.54a-l: Page Momentum Runtime Aggregation Candidate

## Goal

Add a runtime-facing provider that prepares the Page Momentum route/menu payload for the real admin aggregation pipeline without mutating existing source files.

## Safety model

- Existing route/menu aggregators are not edited.
- Runtime aggregation config is isolated at `app/zoosper-page/config/admin_page_momentum_runtime_aggregation_candidate.php`.
- Live mutation remains false.
- The provider exports one route and one menu item when the hook candidate is present.

## Verification

```bash
php8.5 tools/prove-page-admin-momentum-runtime-aggregation-candidate.php
php8.5 tools/audit-page-admin-momentum-runtime-aggregation-readiness.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumRuntimeAggregationCandidateTest.php
php8.5 vendor/bin/pest
```
