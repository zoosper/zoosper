## Phase 1.71a-l: Site context runtime cutover plan

Status: ready to apply

Adds a read-only runtime cutover planner/audit for `SiteContextResolver` and `SiteContextResolverFactory` using the Site lookup boundary foundation.

Safety:

- Read-only planning only.
- No PHP source edits.
- No file movement/deletion.

Verification gates:

- `php8.5 -l tools/plan-site-context-runtime-cutover.php`
- `php8.5 -l tools/audit-site-context-runtime-cutover-plan.php`
- `php8.5 tools/plan-site-context-runtime-cutover.php`
- `php8.5 tools/audit-site-context-runtime-cutover-plan.php`
- `php8.5 tools/audit-site-lookup-boundary-foundation.php`
- `php8.5 tools/audit-site-context-boundary-readiness.php`
- `php8.5 tools/audit-core-feature-coupling.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
