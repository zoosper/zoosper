## Phase 1.43d-f: Selected candidate signature and fixture refinement

Status: ready to apply

Discovers the selected candidate method signature and refines fixture argument placeholders without invoking production services.

Safety:

- Runtime remains disabled by default.
- Selected service is reflected only; not invoked.
- Refined fixture contract remains fixture-only.
- Production runtime interception remains disabled.

Verification gates:

- `php8.5 tools/discover-method-plugin-selected-candidate-signature.php`
- `php8.5 tools/refine-method-plugin-selected-candidate-fixture-contract.php`
- `php8.5 tools/audit-method-plugin-selected-candidate-signature-readiness.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginSelectedCandidateSignatureReadinessTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
