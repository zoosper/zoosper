# Phase 0.98.1 - Service Provider Manifest File Hotfix

Phase 0.98 introduced `ServiceProviderManifestLoader`, but verification requires the manifest file itself to exist.

This hotfix adds:

```text
config/service_providers.php
```

with:

```php
return [
    'providers' => [
        \Zoosper\Core\I18n\I18nServiceProvider::class,
    ],
];
```

This keeps provider discovery explicit and gives `ServiceProviderManifestLoader` an actual manifest to load.
