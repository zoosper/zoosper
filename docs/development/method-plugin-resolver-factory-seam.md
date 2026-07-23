# Phase 1.41j-l: Method Plugin Resolver Factory Seam

## Goal

Introduce a container-aware extension seam without coupling the method plugin system to a concrete container implementation yet.

## Added contracts/classes

- `MethodPluginResolverInterface`: resolves plugin classes into runtime objects.
- `ReflectionMethodPluginResolver`: default no-argument reflection resolver.
- `MethodPluginFactory`: now delegates object resolution to the resolver seam and validates `MethodInterceptorInterface`.

## Safety boundary

Execution still targets only safe sample services. No controller, repository, admin form, entity save flow, or production service is intercepted.

## Verification

```bash
php8.5 tools/prove-method-plugin-resolver-factory.php
php8.5 tools/audit-method-plugin-resolver-factory.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginResolverFactoryTest.php
php8.5 vendor/bin/pest
```

## Next phase

Phase 1.41m-o should harden diagnostics and invalid plugin configuration failures before plugin execution is allowed near real runtime services.
