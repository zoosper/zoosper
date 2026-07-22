# Phase 1.37v.* — Bulk Sites and Site Domains Admin CRUD Plan

## Purpose

Implement as much of the Sites and Site Domains admin CRUD arc as safely possible in one bulk delivery, while avoiding blind overwrites of unknown controller, route, template and service-provider conventions.

## Split inside the bulk phase

```text
1.37v.1 — Sites admin CRUD
1.37v.2 — Site Domains admin CRUD
1.37v.3 — Sidebar/route/admin readiness audit hardening
```

## Phase 1.37v.1 — Sites admin CRUD

Target routes:

```text
/admin/sites
/admin/sites/create
/admin/sites/edit
```

Target behaviours:

```text
- list existing sites
- create site
- edit site
- validate name/code/status/default locale/theme code
- preserve existing site-resolution behaviour
```

## Phase 1.37v.2 — Site Domains admin CRUD

Target routes:

```text
/admin/site-domains
/admin/site-domains/create
/admin/site-domains/edit
```

Target behaviours:

```text
- list domains
- create domain
- edit domain
- assign domain to site
- support host/path_prefix/is_primary/status
- validate duplicate primary-domain rules conservatively
```

## Phase 1.37v.3 — Readiness hardening

Target behaviours:

```text
- sidebar links resolve to real routes
- CRUD route tests exist
- admin middleware/permission coverage exists
- empty state screens explain next steps
- full bin/verify green
```

## Current delivery shape

This patch adds durable bulk audit/inspection tooling, tests and implementation docs. The inspection output should be used to perform the actual source-specific controller/route implementation in the next pass without guessing namespace or template conventions.

## Verification

```bash
php8.5 tools/audit-sites-domains-admin-crud-bulk.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminCrudBulkTest.php
PHP=php8.5 bin/verify
```
