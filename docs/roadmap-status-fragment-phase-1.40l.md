## Phase 1.40l: ConfigLayerSource runtime proof

Status: ready to apply

Updates the exact admin config root override proof to construct real `ConfigLayerSource` instances before calling `ConfigFileLayeredLoader::load($sources)`.

Verification gates:

- `php8.5 tools/discover-config-file-layered-loader-contract.php`
- `php8.5 tools/prove-admin-form-config-root-overrides.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/AdminFormConfigLayerSourceRuntimeProofTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
