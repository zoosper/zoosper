# Phase 1.37v.5 — Sites and Site Domains Runtime CRUD

## Outcome

This phase implements the first runtime admin CRUD pass for Sites and Site Domains using the source conventions exposed by the implementation target inspection.

## Delivered

```text
- SiteAdminController with list/create/edit/update flows.
- SiteDomainAdminController with list/create/edit/update flows.
- SiteDomainRepository for admin-managed host mappings.
- Admin routes for /admin/sites and /admin/site-domains CRUD actions.
- Admin menu links for Sites and Site Domains.
- SiteRepository all() and update() methods for admin CRUD.
- site_domains schema declaration for declarative installs.
- Runtime CRUD implementation tests.
```

## Permission note

Routes use the current existing `settings.manage` permission as the launch-readiness administrative gate. The intended future permission seam remains `site.manage`, and should be introduced when the ACL tree is expanded for site management.

## Verification

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminRuntimeCrudImplementationTest.php
php8.5 tools/audit-sites-domains-admin-crud-runtime.php
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```
