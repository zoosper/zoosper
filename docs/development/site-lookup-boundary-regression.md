# Phase 1.72m-z: Site Lookup Boundary Regression Guard

## Purpose

This phase adds durable guards around the Site lookup boundary after the Page/Site core-feature decoupling arc.

It keeps the repository lean by adding only permanent test/audit coverage, not temporary repair tooling.

## Guards

The phase verifies:

- core Site runtime files do not directly reference `Zoosper\Site\...`;
- `SiteContextResolver` stays behind `SiteLookupInterface`;
- `SiteLookupInterface` keeps active-host compatibility through `findActiveByHost()`;
- `ResolvedSite` keeps DB-backed site context compatibility fields;
- `NullSiteLookup` remains a safe no-op implementation.

## Commands

```bash
php8.5 tools/audit-site-lookup-boundary-regression.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/SiteLookupBoundaryRegressionTest.php
```
