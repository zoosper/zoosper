## Phase 1.40m: ConfigLayerSource constructor proof

Status: ready to apply

Fixes the admin config runtime proof to construct `ConfigLayerSource` using the discovered `($source, $path)` constructor order.

Verification gates:

- `php8.5 tools/discover-config-file-layered-loader-contract.php`
- `php8.5 tools/prove-admin-form-config-root-overrides.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/AdminFormConfigLayerSourceConstructorProofTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
