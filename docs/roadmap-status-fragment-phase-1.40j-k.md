## Phase 1.40j-k: Exact runtime admin config proof

Status: ready to apply

Tightens Phase 1.40g-i by replacing fallback root-override proof with constructor-aware runtime discovery and exact `ConfigFileLayeredLoader::load($sources)` proof attempts.

Verification gates:

- `php8.5 tools/discover-config-file-layered-loader-contract.php`
- `php8.5 tools/prove-admin-form-config-root-overrides.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/AdminFormConfigExactRuntimeProofTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
