# Sites and Site Domains Admin CRUD Operations

Run bulk audit:

```bash
php8.5 tools/audit-sites-domains-admin-crud-bulk.php
```

Generate source-only inspection output:

```bash
php8.5 tools/inspect-sites-domains-admin-crud-bulk.php
```

Remove generated inspection before commit unless intentionally needed:

```bash
rm -f sites-domains-admin-crud-bulk-inspection.txt
```

Run targeted tests:

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminCrudBulkTest.php app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminCrudContractTest.php
```

Run full verification:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```

## Implementation note

Actual CRUD source should be generated only after reviewing the inspection output because route/controller/template conventions can differ between modules.
