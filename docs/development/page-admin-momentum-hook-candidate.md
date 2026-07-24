# Phase 1.52a-l: Page Admin Momentum Hook Candidate

## Goal

Prepare a stable hook payload that the real admin route/menu aggregation pipeline can consume in the next live source patch.

## Safety model

- Existing route/menu aggregators are not overwritten.
- The hook candidate is isolated in `app/zoosper-page/config/admin_page_momentum_hook_candidate.php`.
- Live mutation remains false.
- The hook payload exports one route and one menu item.

## Verification

```bash
php8.5 tools/generate-page-admin-momentum-hook-candidate.php
php8.5 tools/prove-page-admin-momentum-hook-provider.php
php8.5 tools/audit-page-admin-momentum-hook-readiness.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumHookCandidateTest.php
php8.5 vendor/bin/pest
```
