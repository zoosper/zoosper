## Phase 1.40n-p: Admin config layered runtime bridge

Status: ready to apply

Adds `Zoosper\Admin\Form\AdminConfigLayeredFileLoader`, a small admin-facing bridge around `ConfigFileLayeredLoader` and `ConfigLayerSource`, plus a runtime proof and Pest regression guard.

Verification gates:

- `php8.5 tools/prove-admin-config-layered-runtime-bridge.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/AdminConfigLayeredRuntimeBridgeTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
