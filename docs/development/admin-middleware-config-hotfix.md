# Admin Middleware Config Hotfix

## Context

The runtime bootstrap failed with:

```text
Invalid admin middleware entry.
```

The failure occurs while `ApplicationFactory` calls `ModuleAdminMiddlewareLoader`. This can happen even when the unit suite is green because the invalid entry is discovered during web bootstrap.

## Tools

- `tools/audit-admin-middleware-config.php`: identifies invalid or normalisable admin middleware config entries.
- `tools/normalise-admin-middleware-config.php`: rewrites common drift shapes into a flat list of middleware class strings.

## Verification

```bash
php8.5 tools/audit-admin-middleware-config.php
php8.5 tools/normalise-admin-middleware-config.php --dry-run
php8.5 tools/normalise-admin-middleware-config.php --apply
php8.5 tools/audit-admin-middleware-config.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Http/AdminMiddlewareConfigAuditToolTest.php
php8.5 vendor/bin/pest
```

Remove `.phase141mw.bak` files before committing unless the project intentionally tracks rollback artefacts.
