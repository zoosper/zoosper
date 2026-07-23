# Phase 1.40m: ConfigLayerSource Constructor Runtime Proof

## Goal

Phase 1.40l discovered that `ConfigLayerSource` is constructed as `($source, $path)`. This phase fixes the proof script to build source objects in that semantic order before calling `ConfigFileLayeredLoader::load($sources)`.

## Verification

```bash
php8.5 tools/discover-config-file-layered-loader-contract.php
php8.5 tools/prove-admin-form-config-root-overrides.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/AdminFormConfigLayerSourceConstructorProofTest.php
php8.5 vendor/bin/pest
```

## Expected result

```text
Runtime proof used: yes
Fallback proof used: no
Root override proved: yes
Errors: 0
```
