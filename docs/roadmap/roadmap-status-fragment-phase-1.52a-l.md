## Phase 1.52a-l: Page admin momentum hook candidate

Status: ready to apply

Adds a stable hook provider and generated hook candidate config for the Page Momentum admin route/menu integration.

Safety:

- Existing aggregator files are not overwritten.
- Hook candidate is isolated and reversible.
- Live mutation remains false.

Verification gates:

- `php8.5 tools/generate-page-admin-momentum-hook-candidate.php`
- `php8.5 tools/prove-page-admin-momentum-hook-provider.php`
- `php8.5 tools/audit-page-admin-momentum-hook-readiness.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumHookCandidateTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
