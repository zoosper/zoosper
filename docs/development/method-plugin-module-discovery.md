# Phase 1.41g-i: Module-Owned Method Plugin Config Discovery

## Goal

Discover module/package-owned `config/plugins.php` files and load their method plugin definitions into the existing registry/executor flow, while keeping execution limited to a safe sample service.

## Added classes

- `MethodPluginConfigSource`: named config source value object.
- `MethodPluginConfigSourceDiscovery`: discovers `config/plugins.php` under explicit module roots.
- `MethodPluginModuleConfigLoader`: loads definitions from discovered sources.

## Safety boundary

This phase still does not intercept production runtime paths. It only proves module-owned discovery against a dedicated sample service.

## Verification

```bash
php8.5 tools/prove-method-plugin-module-discovery.php
php8.5 tools/audit-method-plugin-module-discovery.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginModuleDiscoveryTest.php
php8.5 vendor/bin/pest
```

## Next phase

Phase 1.41j-l should introduce container-aware plugin instantiation behind a factory interface, then keep execution against sample services until failure/diagnostic behaviour is fully guarded.
