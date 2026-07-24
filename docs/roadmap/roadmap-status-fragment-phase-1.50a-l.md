## Phase 1.50a-l: Page admin momentum aggregator candidate

Status: ready to apply

Adds an isolated runtime candidate config for the Page Momentum admin route/menu integration, plus guarded apply/audit tools, tests, documentation, and roadmap notes.

Safety:

- Existing aggregator files are not overwritten.
- Live mutation remains false.
- Candidate config is isolated and reversible.

Verification gates:

- `php8.5 tools/apply-page-admin-momentum-aggregator-candidate.php`
- `php8.5 tools/audit-page-admin-momentum-aggregator-candidate.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumAggregatorCandidateTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
