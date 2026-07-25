<?php

declare(strict_types=1);

namespace Zoosper\Core\Asset;

/**
 * Builds public URLs for module assets. The `asset()` view helper delegates to
 * this so templates never hard-code the asset route shape.
 */
final class AssetUrlGenerator
{
    public function __construct(
        private readonly string $basePath = '/asset',
    ) {
    }

    /**
     * Build a URL like /asset/zoosper-admin/css/page-momentum.css.
     */
    public function url(string $module, string $relativePath): string
    {
        $module = trim($module, '/');
        $relative = ltrim(str_replace('\\', '/', $relativePath), '/');

        $encoded = implode('/', array_map('rawurlencode', explode('/', $relative)));

        return rtrim($this->basePath, '/') . '/' . rawurlencode($module) . '/' . $encoded;
    }
}
