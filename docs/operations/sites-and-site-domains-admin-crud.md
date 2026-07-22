# Sites and Site Domains Admin CRUD Operations

Run implementation/runtime audits:

```bash
php8.5 tools/audit-sites-domains-admin-crud-implementation.php
php8.5 tools/audit-sites-domains-implementation-blueprint.php
php8.5 tools/audit-sites-domains-admin-crud-runtime.php
```

Generate current source inspection when a future CRUD patch needs to match live conventions:

```bash
php8.5 tools/inspect-sites-domains-implementation-targets.php
```

Run targeted runtime tests:

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminRuntimeCrudImplementationTest.php app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminRuntimeCrudReadinessTest.php
```

Remove generated inspection output before commit:

```bash
rm -f sites-domains-implementation-targets.txt \
      sites-domains-admin-current-source-inspection.txt \
      sites-domains-admin-crud-bulk-inspection.txt
```

Do not commit temporary preparer tools for one-off runtime CRUD generation.

This file should be absent before commit:

```text
tools/prepare-sites-domains-admin-crud-runtime.php
```

Run full verification:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```

## Ownership policy

Sites and Site Domains admin CRUD belongs to `zoosper-site`:

```text
app/zoosper-site/src/Admin/Controller
app/zoosper-site/config/admin_routes.php
app/zoosper-site/config/admin_menu.php
app/zoosper-site/config/controllers.php
app/zoosper-site/config/services.php
```

The central `zoosper-admin` module should provide the shared admin shell, layout, menu loading and generic admin services only.
