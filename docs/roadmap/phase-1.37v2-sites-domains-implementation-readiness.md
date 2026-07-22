# Phase 1.37v.2 — Sites/Site Domains Implementation Readiness

## Purpose

Move from the Phase 1.37v CRUD contract toward source-specific implementation without guessing controller, route, template or service-registration conventions.

## Why this phase exists

The next implementation step should generate actual admin CRUD source for:

```text
/admin/sites
/admin/sites/create
/admin/sites/edit
/admin/site-domains
/admin/site-domains/create
/admin/site-domains/edit
```

However, the correct implementation depends on the current source shape in `app/zoosper-admin`, `app/zoosper-site`, package overrides and route/service-provider conventions.

This phase adds durable readiness tooling so the next bulk implementation pass can be source-specific and safe.

## Subphase map

```text
1.37v.1 — Sites admin CRUD
1.37v.2 — Site Domains admin CRUD
1.37v.3 — Admin sidebar/route readiness hardening
```

## Added tooling

```text
tools/audit-sites-domains-admin-crud-implementation.php
tools/inspect-sites-domains-admin-current-source.php
```

## Deferred roadmap preservation

The Launch Readiness Arc must not hide deferred work. Keep `docs/roadmap/deferred-near-term.md` visible until those items are promoted back into active scope.

## Verification

```bash
php8.5 tools/audit-sites-domains-admin-crud-implementation.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminImplementationReadinessTest.php
PHP=php8.5 bin/verify
```

## Next implementation pass

Use the generated source inspection to implement real CRUD in one batch:

```text
- controllers
- route config
- services/factories
- templates or safe admin HTML following existing conventions
- route/permission tests
```
