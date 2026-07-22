# Phase 1.37v.4 — Sites/Site Domains Runtime CRUD Readiness

## Purpose

Prepare the runtime CRUD implementation pass for Sites and Site Domains without blindly writing controllers/routes against unknown conventions.

The generated `sites-domains-implementation-targets.txt` file is source-only and safe to inspect, but the visible attached content was not enough to safely generate runtime CRUD source without guessing. The runtime patch therefore remains convention-gated.

## Durable tooling

```text
tools/audit-sites-domains-admin-crud-runtime.php
```

The temporary runtime preparer helper is intentionally removed before commit. Source-specific CRUD generation should happen in a later patch after inspection output is reviewed locally.

## Required implementation targets

```text
/admin/sites
/admin/sites/create
/admin/sites/edit
/admin/site-domains
/admin/site-domains/create
/admin/site-domains/edit
```

## Next source-specific build

After reviewing the inspection output locally, the next patch should generate:

```text
- SiteAdminController or SitesAdminController
- SiteDomainAdminController or SiteDomainsAdminController
- SiteDomain model/repository if absent
- admin route config entries
- controller factory/service wiring
- route/readiness tests
```

## Verification

```bash
php8.5 tools/audit-sites-domains-admin-crud-runtime.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminRuntimeCrudReadinessTest.php
PHP=php8.5 bin/verify
```
