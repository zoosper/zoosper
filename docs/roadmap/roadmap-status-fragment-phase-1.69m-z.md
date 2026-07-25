## Phase 1.69m-z: Guarded ApplicationFactory fallback cutover

Status: ready to apply

Adds a dry-run-first patcher to replace the direct `PageController` dependency in `ApplicationFactory` with the core-owned fallback handler boundary.

Safety:

- Dry-run by default.
- Exact-match guarded patch.
- Backup written before apply.
- No database changes.

Verification gates:

- `php8.5 -l tools/apply-application-factory-fallback-cutover.php`
- `php8.5 tools/apply-application-factory-fallback-cutover.php`
- optional: `php8.5 tools/apply-application-factory-fallback-cutover.php --apply`
- `php8.5 tools/audit-page-fallback-runtime-cutover-readiness.php`
- `php8.5 tools/audit-core-feature-coupling.php`
- `php8.5 tools/plan-core-feature-decoupling-remediation.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
