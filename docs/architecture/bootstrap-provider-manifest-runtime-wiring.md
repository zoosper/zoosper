# Phase 0.99 - Bootstrap Provider Manifest Runtime Wiring

This phase wires `ServiceProviderManifestLoader` into the concrete application bootstrap/container creation point.

## Why

Previous phases created:

```text
config/service_providers.php
ServiceProviderManifestLoader
I18nServiceProvider
```

Phase 0.99 makes the manifest loader part of runtime bootstrap, so manifest-declared providers can register services in the application container.

## Safety

The phase uses an idempotent apply tool to preserve the current `ApplicationFactory.php` and insert only the manifest loader call. A backup is written before the file is modified.
