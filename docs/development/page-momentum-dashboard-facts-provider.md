# Page Momentum Dashboard Facts Provider

This pack adds a read-only dashboard facts provider for the Page Momentum dashboard.

## Facts

- Total pages
- Published pages
- Draft pages
- Disabled pages
- Missing SEO title
- Missing SEO description
- URL rewrite count when available
- Site count when available
- Domain count when available

## Safety

The provider performs read-only SELECT/PRAGMA/metadata inspection only. Optional tables fail softly.

## Verification

```bash
php8.5 tools/audit-page-momentum-dashboard-facts-provider.php
php8.5 vendor/bin/pest app/zoosper-page/tests/Unit/Admin/PageMomentum/PageMomentumDashboardFactsProviderTest.php
```
