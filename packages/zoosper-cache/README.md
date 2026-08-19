# zoosper/cache

Zoosper-owned cache boundary backed by Marko file and Redis drivers.

## Responsibilities
- Own `Zoosper\Cache\Contract\CacheInterface` for application consumers.
- Adapt Marko cache drivers without exposing Marko contracts to Core or feature modules.
- Construct file and Redis drivers from existing `cache.*` and `encryption.*` configuration.
- Own cache-specific Marko Encryption use for Redis value signing.

## Dependencies
The package owns `marko/cache`, `marko/cache-file`, `marko/cache-redis`, `marko/config`, and `marko/encryption`. It intentionally has no dependency on `zoosper/core`.

## Extension points
Consumers type against the Zoosper cache contract. Projects may replace its service binding without changing Core or Page.

## Testing
Run `php8.5 vendor/bin/pest packages/zoosper-cache/tests` and the full repository suite.


## Operational notes

The configured file-cache directory must be writable by PHP-FPM and console users. Redis deployments must provide reachable connection settings and a non-empty signing key through the existing configuration boundary. Cache failures in the frontend fallback decorator remain fail-open and must never prevent a page response. Preserve the established `cache.*`, `encryption.*`, and `page_cache.*` keys when replacing drivers or bindings.

Run the full repository suite and quality gate before release:

```bash
zcomposer test
php8.5 tools/gate.php
```

Also validate Composer manifests, compile the module manifest, verify manifest freshness, and retain the real file-driver round-trip and Redis object-graph tests.
