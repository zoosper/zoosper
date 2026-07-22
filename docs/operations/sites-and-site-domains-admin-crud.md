# Sites and Site Domains Admin CRUD Operations

Run implementation-readiness tools:

```bash
php8.5 tools/audit-sites-domains-admin-crud-implementation.php
php8.5 tools/audit-sites-domains-implementation-blueprint.php
php8.5 tools/audit-sites-domains-admin-crud-runtime.php
```

Generate current source inspection:

```bash
php8.5 tools/inspect-sites-domains-implementation-targets.php
```

Use the generated output locally, then remove before commit:

```bash
rm -f sites-domains-implementation-targets.txt \
      sites-domains-admin-current-source-inspection.txt \
      sites-domains-admin-crud-bulk-inspection.txt
```

Run targeted runtime-readiness tests:

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminRuntimeCrudReadinessTest.php
```

Run full verification:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```

## Cleanup policy

Do not commit temporary preparer tools for one-off runtime CRUD generation.

This file should be absent before commit:

```text
tools/prepare-sites-domains-admin-crud-runtime.php
```

The durable artefacts for Phase 1.37v.4 are:

```text
- tools/audit-sites-domains-admin-crud-runtime.php
- runtime-readiness tests
- implementation-readiness documentation
```

Actual CRUD source should be generated only when the inspection output has enough convention detail to safely match the current controller, route, service and template patterns.
