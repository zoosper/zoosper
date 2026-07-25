# Phase 1.74a-l: Site Lookup Service Binding Regression Guard

## Purpose

This phase adds a permanent guard for the Site lookup service binding completed in Phase 1.73m-z.

## What it protects

The guard verifies that:

- the Site module service config visibly binds `SiteLookupInterface` to `DatabaseSiteLookup`;
- the binding depends on `SiteRepository` from the Site module side;
- core service config does not own the Site-module database adapter binding;
- core Site runtime source remains free of direct `Zoosper\Site\...`, `SiteRepository`, and `DbSite` references.

## Commands

```bash
php8.5 tools/audit-site-lookup-service-binding-regression.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/SiteLookupServiceBindingRegressionTest.php
```
