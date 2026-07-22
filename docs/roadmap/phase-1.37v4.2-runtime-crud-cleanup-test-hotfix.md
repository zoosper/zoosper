# Phase 1.37v.4.2 — Runtime CRUD Cleanup Test Hotfix

The Phase 1.37v.4.1 cleanup documentation correctly mentioned the temporary helper file by name:

```text
tools/prepare-sites-domains-admin-crud-runtime.php
```

The test incorrectly asserted that the operations document should not contain that string. That made the test contradict the cleanup policy, because the documentation must name the file that should be absent before commit.

## Outcome

```text
- Keeps the assertion that the temporary helper file itself is absent.
- Updates the documentation test to assert the cleanup policy explicitly names the file.
- Keeps the durable runtime audit as the only committed root tool for this phase.
```

## Verification

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Admin/SitesDomainsAdminRuntimeCrudReadinessTest.php
php8.5 tools/audit-sites-domains-admin-crud-runtime.php
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```
