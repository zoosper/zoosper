<?php

declare(strict_types=1);

/**
 * Root-level configuration for the module asset pipeline (Phase C1/C2).
 *
 * Deliberately named 'asset_pipeline' rather than 'assets': each MODULE's own
 * config/assets.php returns a completely different shape (a map of
 * module-name => absolute-assets-directory, consumed directly by
 * ModuleAssetManifestLoader, bypassing ConfigRepository entirely). Using a
 * distinct domain name here avoids any risk of this root-level settings file
 * being merged together with those per-module manifest files under a single
 * generic 'assets' key.
 *
 * @return array{cache_max_age: int, cache_immutable: bool}
 */
return [
    // How long (in seconds) a browser/CDN may cache a served module asset.
    // Default matches the previous hard-coded value (1 year). Lower this if
    // you need faster propagation of asset changes without relying solely on
    // cache-busting query strings; raise it for maximum caching efficiency.
    'cache_max_age' => 31536000,

    // Whether to add the `immutable` Cache-Control directive. When true,
    // browsers that support it will NEVER revalidate the asset for the
    // lifetime of cache_max_age, even on a hard refresh — appropriate only
    // when every asset URL is reliably cache-busted (e.g. a `?v=` query
    // string) on content change. Set to false if you are not yet consistently
    // versioning asset URLs.
    'cache_immutable' => true,
];








