# Asset Pipeline Configuration

## Cache TTL / immutability

`config/asset_pipeline.php` (root-level):

```php
return [
    'cache_max_age' => 31536000, // seconds
    'cache_immutable' => true,
];
```

Read once in `ApplicationFactory::create()` and passed into `AssetController`'s
constructor. Change these values to tune caching without touching any code.

## Cache-busting query strings

`AssetUrlGenerator::url($module, $path, $version = null)` appends `?v=$version`
when a version is given. Use your module's own versioning convention (a
literal string, a content hash, a timestamp) as the third argument.

## HEAD requests

The asset route responds to both GET and HEAD, per RFC 9110. HEAD returns the
identical status/headers as GET, with no body.
