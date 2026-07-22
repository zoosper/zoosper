# Phase 1.37v.4.1 — Runtime CRUD Helper Cleanup

Phase 1.37v.4 introduced runtime CRUD readiness tooling for Sites and Site Domains, but the tools inventory classified the preparer helper as `REVIEW`:

```text
tools/prepare-sites-domains-admin-crud-runtime.php
```

The durable tool is the runtime audit. The preparer was a one-off helper and should not be committed unless it is explicitly promoted to durable tooling.

## Outcome

```text
- Keep tools/audit-sites-domains-admin-crud-runtime.php.
- Remove tools/prepare-sites-domains-admin-crud-runtime.php before commit.
- Update tests so the temporary preparer is intentionally absent.
- Update operations docs with the cleanup policy.
```

## Verification

```bash
rm -f tools/prepare-sites-domains-admin-crud-runtime.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminRuntimeCrudReadinessTest.php
php8.5 tools/audit-sites-domains-admin-crud-runtime.php
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```
