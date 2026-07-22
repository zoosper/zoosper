# Phase 1.37v — Sites and Site Domains Admin CRUD

## Purpose

Make Zoosper configurable from the admin UI by implementing the first real Launch Readiness CRUD screens for site and domain management.

## Scope

```text
/admin/sites
/admin/sites/create
/admin/sites/edit
/admin/site-domains
/admin/site-domains/create
/admin/site-domains/edit
```

## Expected outcomes

```text
- Admin can list sites.
- Admin can create and edit a site.
- Admin can list site domains.
- Admin can create and edit a site domain.
- A site domain maps to a site.
- Routes are protected by admin middleware and appropriate permission checks.
- Existing site resolution remains intact.
```

## Non-goals

```text
- full tenant isolation
- multi-database support
- billing/org tenancy
- automatic DNS verification
- theme assignment UI beyond minimal theme_code field if needed
- settings storage UI
```

## Verification

```bash
php8.5 tools/audit-sites-domains-admin-crud.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminCrudContractTest.php
PHP=php8.5 bin/verify
```

## Follow-up

```text
Phase 1.37w — Core settings storage and admin settings UI
```
