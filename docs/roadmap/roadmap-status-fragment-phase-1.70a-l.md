## Phase 1.70a-l: Site context boundary readiness

Status: ready to apply

Adds read-only Site context boundary audit and cutover plan tooling after the Page fallback decoupling slice.

Safety:

- Read-only audit/planning only.
- No runtime PHP changes.
- No file movement/deletion.

Verification gates:

- `php8.5 -l tools/audit-site-context-boundary-readiness.php`
- `php8.5 -l tools/plan-site-context-boundary-cutover.php`
- `php8.5 tools/audit-site-context-boundary-readiness.php`
- `php8.5 tools/plan-site-context-boundary-cutover.php`
- `php8.5 tools/audit-core-feature-coupling.php`
- `php8.5 tools/plan-core-feature-decoupling-remediation.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
