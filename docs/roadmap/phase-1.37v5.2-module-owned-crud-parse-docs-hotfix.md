# Phase 1.37v.5.2 — Module-owned CRUD Parse and Docs Hotfix

## Problem

The 1.37v.5.1 patch copied inspection marker lines into PHP config files:

```text
Missing: packages/zoosper-admin/config/routes.php
Missing: packages/zoosper-admin/config/services.php
Missing: packages/zoosper-admin/config/controllers.php
```

That created parse errors in `app/zoosper-admin/config/admin_routes.php` and `app/zoosper-admin/config/controllers.php`.

The operations document also dropped strings still required by `SitesDomainsAdminRuntimeCrudReadinessTest`.

## Outcome

```text
- Rewrites central admin config files as valid PHP.
- Keeps Sites/Site Domains route/menu/controller ownership in zoosper-site.
- Restores SiteResolver registration while adding SiteDomainRepository registration.
- Restores the source-inspection and temporary-helper cleanup text required by readiness tests.
```

## Verification

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminRuntimeCrudImplementationTest.php app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminRuntimeCrudReadinessTest.php
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```
