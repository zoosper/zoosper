# Phase 1.37u.1 — Admin navigation helper cleanup

Phase 1.37u introduced a durable audit plus temporary helper tooling for replacing launch-readiness admin links and generating documentation stubs.

The tools inventory classified the one-off helpers as non-durable:

```text
DELETE_NOW: tools/apply-admin-launch-readiness-navigation.php
REVIEW:     tools/scaffold-admin-launch-readiness-stubs.php
```

## Outcome

```text
- Keep tools/audit-admin-launch-readiness-navigation.php as durable ops tooling.
- Remove temporary apply/scaffold helper tools before commit.
- Keep generated launch-readiness documentation stubs.
- Update tests so they assert the temporary helper tools are not required.
- Update operations docs with the cleanup policy.
```

## Verification

```bash
rm -f tools/apply-admin-launch-readiness-navigation.php tools/scaffold-admin-launch-readiness-stubs.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Routing/AdminLaunchReadinessNavigationTest.php
php8.5 tools/audit-admin-launch-readiness-navigation.php
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```
