# Modular Asset Auto-Discovery

## Why

Phase 1.90 gave us a pipeline that serves module assets in place. This phase makes
it automatic: modules self-register their asset directories through a config
manifest, so no core edits are needed to onboard a module's assets.

This mirrors the config-discovery Zoosper already uses for services
(`ServiceProviderLoader`) and events (`ModuleEventListenerLoader`).

## The convention

Each module ships `config/assets.php`:

```php
<?php
declare(strict_types=1);
return [
    'zoosper-admin' => dirname(__DIR__) . '/resources/assets',
];
```

- Key: the logical module name used in `asset('name', 'path')`.
- Value: the absolute directory that holds the module's public assets.

## How discovery works

`ModuleAssetManifestLoader::registerInto($registry)`:

1. iterates every enabled module;
2. requires its `config/assets.php` when present;
3. merges each entry into the `AssetModuleRegistry` (with validation).

The pure merge step is `ModuleAssetManifestLoader::mergeDefinitions()`, which is
unit-tested directly with fixture manifests.

## One-time wiring (core services.php)

```php
use Zoosper\Core\Asset\AssetModuleRegistry;
use Zoosper\Core\Asset\ModuleAssetManifestLoader;
use Zoosper\Core\Module\ModuleRegistry;

AssetModuleRegistry::class => static function (ServiceContainer $services): AssetModuleRegistry {
    $registry = new AssetModuleRegistry();
    (new ModuleAssetManifestLoader($services->get(ModuleRegistry::class)))->registerInto($registry);
    return $registry;
},
```

Combined with the Phase 1.90 route `GET /asset/{module}/{path:.+}` and the
`asset()` helper, everything is automatic from here.

## Third-party module example

```
app/acme-widgets/
  config/assets.php          -> return ['acme-widgets' => dirname(__DIR__) . '/resources/assets'];
  resources/assets/css/widget.css
```

Template usage:

```php
<link rel="stylesheet" href="<?= asset('acme-widgets', 'css/widget.css') ?>">
```

Drop the folder in — served. Remove it — gone. No copying, no core changes.

## Using it for the dashboard

Once wired, the page-momentum layout can reference its stylesheet the modular way:

```php
<link rel="stylesheet" href="<?= asset('zoosper-admin', 'css/page-momentum.css') ?>">
```
