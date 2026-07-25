## Phase 1.72a-l: Core feature decoupling closure

Status: ready to apply

Adds a final closure audit for the current Page fallback + Site lookup decoupling arc.

Safety:

- Read-only audit only.
- No PHP source edits.
- No file movement/deletion.

Verification gates:

- `php8.5 -l tools/audit-core-feature-decoupling-closure.php`
- `php8.5 tools/audit-core-feature-coupling.php`
- `php8.5 tools/audit-core-feature-decoupling-closure.php`
- `php8.5 tools/audit-site-context-boundary-readiness.php`
- `php8.5 tools/plan-site-context-runtime-cutover.php`
- `php8.5 tools/apply-site-context-runtime-cutover.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
