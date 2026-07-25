## Phase 1.67m-z: Core feature decoupling remediation plan

Status: ready to apply

Adds a read-only planner that turns core-feature coupling audit findings into recommended remediation buckets.

Safety:

- Read-only planning only.
- No runtime source edits.
- No file movement/deletion.

Verification gates:

- `php8.5 -l tools/plan-core-feature-decoupling-remediation.php`
- `php8.5 tools/audit-core-feature-coupling.php`
- `php8.5 tools/plan-core-feature-decoupling-remediation.php`
- `php8.5 tools/audit-page-momentum-cleanup-closure.php`
- `php8.5 tools/audit-repository-lean-hygiene.php --strict`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
