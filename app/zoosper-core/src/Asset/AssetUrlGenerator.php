<?php

declare(strict_types=1);

namespace Zoosper\Core\Asset;

/**
 * Builds public URLs for module assets. The `asset()` view helper delegates to
 * this so templates never hard-code the asset route shape.
 *
 * Phase C2: url() gains an optional $version parameter that appends a `?v=`
 * cache-busting query string — the flexibility to force a browser to reload
 * new content without relying on the 1-year immutable Cache-Control alone.
 * Fully backward compatible: calling url($module, $path) with no third
 * argument produces EXACTLY the same output as before this change.
 */
final class AssetUrlGenerator
{
    public function __construct(
        private readonly string $basePath = '/asset',
    ) {
    }

    /**
     * Build a URL like /asset/zoosper-admin/css/page-momentum.css, optionally
     * with a `?v=$version` cache-busting query string appended.
     *
     * @param string|int|null $version When provided (non-null, non-empty
     *        after casting to string), appended as `?v=<version>`. Pass a
     *        content hash, a build/asset version string, or a timestamp —
     *        whatever your module's own versioning convention uses.
     */
    public function url(string $module, string $relativePath, string|int|null $version = null): string
    {
        $module = trim($module, '/');
        $relative = ltrim(str_replace('\\', '/', $relativePath), '/');

        $encoded = implode('/', array_map('rawurlencode', explode('/', $relative)));

        $url = rtrim($this->basePath, '/') . '/' . rawurlencode($module) . '/' . $encoded;

        $versionString = $version !== null ? trim((string) $version) : '';
        if ($versionString !== '') {
            $url .= '?v=' . rawurlencode($versionString);
        }

        return $url;
    }
}
