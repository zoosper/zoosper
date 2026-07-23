# Admin Middleware Closure Entry Hotfix

## Context

Runtime bootstrap failed because `ModuleAdminMiddlewareLoader` rejected a Closure value in `app/zoosper-auth/config/admin_middleware.php`.

The concrete invalid entry discovered by audit was:

```text
[0] invalid: Closure
```

The valid entries were middleware class strings:

```text
Zoosper\Auth\Http\AuthenticationMiddleware
Zoosper\Auth\Http\CsrfMiddleware
```

## Fix

`tools/remove-closure-admin-middleware-entries.php` removes Closure values from admin middleware config while preserving valid class-string middleware entries.

## Verification

```bash
php8.5 tools/remove-closure-admin-middleware-entries.php --dry-run
php8.5 tools/remove-closure-admin-middleware-entries.php --apply
php8.5 tools/audit-admin-middleware-config.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Http/AdminMiddlewareClosureEntryRemovalTest.php
php8.5 vendor/bin/pest
```

Remove `.phase141mwclosure.bak` files before commit unless intentionally tracking rollback artefacts.
