## Phase 1.40g-i: Admin config root override proof

Status: ready to apply

Adds contract discovery and root override proof tooling for admin form/UI config layering after runtime migration markers were applied in Phase 1.40d-f.

Verification gates:

- `php8.5 tools/discover-config-file-layered-loader-contract.php`
- `php8.5 tools/prove-admin-form-config-root-overrides.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/AdminFormConfigRootOverrideProofTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
