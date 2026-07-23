# Phase 1.41d-f: Method Plugin Discovery and Sample Service Proof

## Goal

Prove method plugins can be discovered from PHP config files, instantiated, ordered, and executed against a safe sample service without touching controllers or production runtime hot paths.

## Added classes

- `MethodPluginFactory`: creates plugin objects and validates they implement `MethodInterceptorInterface`.
- `MethodPluginExecutor`: resolves registered plugin definitions and executes the interceptor chain.
- `MethodPluginFileConfigLoader`: loads method plugin definitions from PHP config files.

## Verification

```bash
php8.5 tools/prove-method-plugin-sample-service.php
php8.5 tools/audit-method-plugin-discovery.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginDiscoveryAndSampleServiceTest.php
php8.5 vendor/bin/pest
```

## Safety boundary

This phase proves the mechanism only against a sample service. It does not intercept controllers, repositories, save flows, admin forms, or any production service.

## Next phase

Phase 1.41g-i should discover module-owned `config/plugins.php` files and load them into the registry with deterministic ordering and diagnostics.
