# Modular Asset Pipeline

## The problem it solves

A true modular CMS must not require hand-copying a module's CSS/JS into `public/`.
A dropped-in module should ship its own `resources/assets/` and have them served
automatically. This pipeline makes that real.

## The pieces

| Class | Role |
| --- | --- |
| `AssetModuleRegistry` | Maps a logical module name to its absolute assets dir. Modules self-register. |
| `AssetResolver` | Safely maps (module, path) to a real file; blocks traversal; allowlists types. |
| `ResolvedAsset` | Immutable metadata: path, mime, size, etag, mtime. |
| `AssetController` | One route handler; streams the file with cache + 304 handling. |
| `AssetUrlGenerator` | Builds `/asset/{module}/{path}` URLs; the `asset()` helper uses it. |

## One-time core wiring

### 1. Register the pipeline in core services.php

```php
use Zoosper\Core\Asset\AssetModuleRegistry;
use Zoosper\Core\Asset\AssetResolver;
use Zoosper\Core\Asset\AssetController;
use Zoosper\Core\Asset\AssetUrlGenerator;

AssetModuleRegistry::class => static function (): AssetModuleRegistry {
    // Modules contribute their own entries (see "Third-party modules" below).
    // Core can seed the admin module here:
    return new AssetModuleRegistry([
        'zoosper-admin' => dirname(__DIR__, 2) . '/zoosper-admin/resources/assets',
    ]);
},
AssetResolver::class => static fn ($c) => new AssetResolver($c->get(AssetModuleRegistry::class)),
AssetController::class => static fn ($c) => new AssetController($c->get(AssetResolver::class)),
AssetUrlGenerator::class => static fn () => new AssetUrlGenerator('/asset'),
```

### 2. Add ONE route

```php
// GET /asset/{module}/{path}   (path may contain slashes)
$router->get('/asset/{module}/{path:.+}', function ($request) use ($c) {
    $result = $c->get(AssetController::class)->serve(
        $request->route('module'),
        $request->route('path'),
        $request->headers()
    );

    // Adapt $result (status, headers, body/filePath) to your Response object.
    return Response::fromArray($result);
});
```

### 3. Register the `asset()` view helper

```php
function asset(string $module, string $path): string
{
    return app(AssetUrlGenerator::class)->url($module, $path);
}
```

## Using it in a layout (no manual copying!)

```php
<link rel="stylesheet" href="<?= asset('zoosper-admin', 'css/page-momentum.css') ?>">
```

That resolves to `/asset/zoosper-admin/css/page-momentum.css`, served straight
from `app/zoosper-admin/resources/assets/css/page-momentum.css`.

## Third-party modules (the whole point)

A third-party module ships:

```
app/acme-widgets/
  config/assets.php        ->  returns ['acme-widgets' => __DIR__ . '/../resources/assets']
  resources/assets/css/widget.css
```

Your module loader merges each module's `config/assets.php` into the
`AssetModuleRegistry` at boot. The author writes in their templates:

```php
<link rel="stylesheet" href="<?= asset('acme-widgets', 'css/widget.css') ?>">
```

No core edits. No copying into public/. Drop the folder in — done.

## Security

- Path traversal (`../`, absolute paths, null bytes) is rejected.
- The resolved real path must stay inside the module's assets base dir.
- Only allowlisted extensions are served, each mapped to a safe MIME type.
- `X-Content-Type-Options: nosniff` is always sent.

## Performance

- Long-lived immutable `Cache-Control` (1 year) + `ETag` + `304 Not Modified`.
- Optional future step: a `bin/zoosper assets:link` that SYMLINKS module asset
  dirs under `public/` for direct nginx serving in production — still zero copying.

## Why this keeps Zoosper lean

Assets stay co-located with their module. Nothing is duplicated into public/.
Removing a module removes its assets automatically. This is the modularity the
project set out to prove before public release.
