# Sites and Site Domains Admin CRUD Operations

Run the CRUD audit:

```bash
php8.5 tools/audit-sites-domains-admin-crud.php
```

Generate a local source inspection file if needed:

```bash
php8.5 tools/inspect-sites-domains-admin-crud.php
```

Remove the generated inspection output before commit unless intentionally needed:

```bash
rm -f sites-domains-admin-crud-inspection.txt
```

Run targeted tests:

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminCrudContractTest.php
```

Run full verification:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```

## Build guidance

Keep the first CRUD implementation additive:

```text
- reuse existing SiteRepository and schema where available
- add SiteDomain schema only if absent
- avoid destructive schema changes
- use one permission seam for both screens initially
- keep generated inspection files out of git
```
