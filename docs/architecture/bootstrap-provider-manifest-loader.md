# Phase 0.98 - Bootstrap Provider Manifest Loader

Zoosper now has a reusable loader for `config/service_providers.php`.

## New class

```text
Zoosper\Core\Bootstrap\ServiceProviderManifestLoader
```

## Responsibilities

```text
Load config/service_providers.php
Read providers from the manifest
Instantiate providers conservatively
Call register(object $container)
Return the number of loaded providers
```

## Constructor support

The loader currently resolves provider constructor parameters named:

```text
basePath
i18nConfig
```

This supports `I18nServiceProvider` while keeping unknown providers explicit and safe.
