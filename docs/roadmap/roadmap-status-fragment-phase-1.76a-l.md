## Phase 1.76a-l: Durable tool registry integration

Status: ready to apply

Connects the architecture foundation gate aggregator to `config/durable_tools.php` so durable tools are not hardcoded in multiple places.

Safety:

- Tooling/test/docs only.
- No runtime application source changes.
- No database changes.

Verification gates:

- `php8.5 -l tools/audit-architecture-foundation-gates.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/ArchitectureFoundationDurableToolIntegrationTest.php`
- `php8.5 tools/audit-durable-tool-registry.php`
- `php8.5 tools/audit-architecture-foundation-gates.php`
- `php8.5 tools/verify-architecture-foundation.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
