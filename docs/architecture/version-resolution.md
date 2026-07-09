# CMS Version Resolution

Zoosper's visible version label should come from one place instead of hardcoded strings inside renderers.

## Source order

`CmsVersion` resolves the version from:

1. `config/app.php` key `version`
2. `CMS_VERSION` environment variable
3. fallback `0.12.0-dev`

## Usage

```php
$version = new CmsVersion($config);
echo $version->label();
```

The page renderer now uses this service, so old text such as `Rendered by Zoosper Phase 0.3` is replaced with the configured CMS version label.
