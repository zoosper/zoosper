# Phase 1.40n-p: Admin Config Layered Runtime Bridge

## Goal

Provide an admin-facing bridge around the proven core config layering contract so admin form/UI config loaders can move away from direct `require` calls and use the shared layered runtime path.

## Proven contract

- `ConfigLayerSource` is constructed as `($source, $path)`.
- `ConfigFileLayeredLoader::load($sources)` returns a `LayeredConfigResult` containing the merged config payload.
- Later sources override earlier sources while preserving unspecified defaults.

## Verification

```bash
php8.5 tools/prove-admin-config-layered-runtime-bridge.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/AdminConfigLayeredRuntimeBridgeTest.php
php8.5 vendor/bin/pest
```

## Next step

After this bridge is green, the safest next phase is to wire `AdminFormConfigAggregator` first, because previous discovery showed it contains the strongest admin form config loading signals.
