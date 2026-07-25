## Phase 1.75n-z: Durable tool registry guard

Status: ready to apply

Adds a durable tool registry and audit so cleanup phases do not accidentally delete tools that are part of the verified architecture/test contract.

Safety:

- Tooling/config only.
- No runtime PHP source changes.
- No database changes.

Verification gates:

- `php8.5 -l config/durable_tools.php`
- `php8.5 -l tools/audit-durable-tool-registry.php`
- `php8.5 tools/audit-durable-tool-registry.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/DurableToolRegistryTest.php`
- `php8.5 tools/audit-architecture-foundation-gates.php`
- `php8.5 tools/verify-architecture-foundation.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
