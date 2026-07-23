# Phase 1.40l: ConfigLayerSource Runtime Proof

## Goal

The Phase 1.40j-k exact runtime proof proved that `ConfigFileLayeredLoader::load(array $sources)` expects `ConfigLayerSource` instances. This phase updates the proof to build real `ConfigLayerSource` objects and call the runtime loader without fallback success.

## Verification

```bash
php8.5 tools/discover-config-file-layered-loader-contract.php
php8.5 tools/prove-admin-form-config-root-overrides.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/AdminFormConfigLayerSourceRuntimeProofTest.php
php8.5 vendor/bin/pest
```

## Expected proof

```text
Runtime proof used: yes
Fallback proof used: no
Root override proved: yes
Errors: 0
```
