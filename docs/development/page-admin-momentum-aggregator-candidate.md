# Phase 1.50a-l: Page Admin Momentum Aggregator Candidate

## Goal

Create an isolated runtime candidate config for the Page Momentum admin route/menu integration without overwriting existing route/menu aggregator files.

## Safety model

- Existing aggregator files are not overwritten.
- Candidate config is isolated at `app/zoosper-page/config/admin_page_momentum_runtime_candidate.php`.
- Candidate exports one route and one menu item from the active metadata.
- Live mutation remains false.

## Verification

```bash
php8.5 tools/apply-page-admin-momentum-aggregator-candidate.php
php8.5 tools/audit-page-admin-momentum-aggregator-candidate.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumAggregatorCandidateTest.php
php8.5 vendor/bin/pest
```

## Next phase

Phase 1.50m-z should wire this candidate into the actual admin route/menu aggregation pipeline once the consuming extension point is confirmed.
