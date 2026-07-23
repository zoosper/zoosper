# Phase 1.41a-c: Method Plugin/Interceptor Foundation

## Goal

Add the first non-invasive foundation for Zoosper's method plugin/interceptor system. This is one of the remaining modularity gaps after Phase 1.40 config layering.

## What is included

- `MethodInvocation`: immutable method call context.
- `MethodInterceptorInterface`: around-style interceptor contract.
- `CallableMethodInterceptor`: lightweight callback adapter for tests and future config-created plugins.
- `MethodInterceptorChain`: deterministic chain runner.
- `MethodPluginDefinition`: declarative plugin definition.
- `MethodPluginRegistry`: ordered registry by `subject::method`.
- `MethodPluginConfigLoader`: PHP array config to definition loader.

## Design constraints

1. No runtime services/controllers are intercepted yet.
2. Interceptors are explicit and deterministic.
3. Disabled plugin config entries are ignored.
4. Sort order is ascending and stable enough for deterministic tests.
5. The foundation is suitable for later module config discovery via `config/plugins.php`.

## Verification

```bash
php8.5 tools/audit-method-plugin-foundation.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginFoundationTest.php
php8.5 vendor/bin/pest
```

## Next phase

Phase 1.41d-f should add module config discovery for `config/plugins.php`, instantiate plugin classes through the container, and run the chain against a safe sample service before any controller/runtime path is touched.
