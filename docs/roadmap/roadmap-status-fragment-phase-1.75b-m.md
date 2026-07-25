## Phase 1.75b-m: Role admin cutover tools restoration

Status: ready to apply

Restores durable RoleAdmin cutover tools required by existing tests and allowlists them in the architecture foundation gate aggregator.

Verification gates:

- `php8.5 -l tools/apply-role-admin-latte-cutover.php`
- `php8.5 -l tools/apply-role-admin-markup-view-cutover.php`
- `php8.5 -l tools/audit-architecture-foundation-gates.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Admin/RoleAdminLatteCutoverExecutorTest.php app/zoosper-core/tests/Unit/Admin/RoleAdminMarkupViewCutoverTest.php`
- `php8.5 tools/audit-architecture-foundation-gates.php`
- `php8.5 tools/verify-architecture-foundation.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
