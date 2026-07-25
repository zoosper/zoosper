## Phase 1.69a-l: ApplicationFactory fallback cutover plan

Status: ready to apply

Adds a read-only planner and audit for the upcoming `ApplicationFactory` fallback-handler cutover.

Safety:

- Read-only planning only.
- No runtime source edits.
- No file movement/deletion.

Verification gates:

- `php8.5 -l tools/plan-application-factory-fallback-cutover.php`
- `php8.5 -l tools/audit-application-factory-fallback-cutover-plan.php`
- `php8.5 tools/plan-application-factory-fallback-cutover.php`
- `php8.5 tools/audit-application-factory-fallback-cutover-plan.php`
- `php8.5 tools/audit-page-fallback-runtime-cutover-readiness.php`
- `php8.5 tools/audit-core-feature-coupling.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
