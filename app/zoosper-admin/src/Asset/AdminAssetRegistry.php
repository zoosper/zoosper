<?php

declare(strict_types=1);

namespace Zoosper\Admin\Asset;

use RuntimeException;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Loads module-owned admin asset declarations.
 *
 * Modules can contribute CSS and JavaScript through `config/admin_assets.php`.
 * This keeps admin UI dependencies marketplace-friendly and avoids hard-coding
 * feature assets inside a central layout or bootstrap class.
 *
 * Phase B4 fix: two independently-authored modules (zoosper-admin and
 * zoosper-page) both declared an asset entry for the physical file
 * zoosper-tag-selector.css under different handles, so BOTH were being
 * rendered as separate <link> tags (a real, confirmed duplicate <link> bug,
 * not a hypothetical). all() now de-duplicates by the asset's resolved path
 * WITHOUT its query string (so admin.css?v=1 and admin.css?v=2 are correctly
 * treated as the SAME physical asset), keeping the first occurrence in final
 * sort order (lowest sort_order, then handle) and silently dropping later
 * duplicates. This is deliberately NOT a hard error: two modules coincidentally
 * declaring the same shared vendor asset is a legitimate marketplace scenario,
 * not necessarily a misconfiguration — it should be tolerated, not fatal.
 */
final readonly class AdminAssetRegistry
{
    public function __construct(private ModuleRegistry $modules)
    {
    }

    /**
     * Return all enabled admin assets sorted by sort order and handle, with
     * duplicate physical assets (same path, different handle/module)
     * collapsed to a single entry. Screen applicability is evaluated before
     * physical-path de-duplication so an inapplicable early declaration cannot
     * suppress an applicable declaration from another module.
     *
     * @return list<AdminAsset>
     */
    public function all(?string $screen = null): array
    {
        $assets = [];

        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('admin_assets.php');
            if (!is_file($file)) {
                continue;
            }

            $config = $this->loadConfig($file);
            if (!is_array($config)) {
                throw new RuntimeException('Admin asset config must return an array: ' . $file);
            }

            $declarations = $this->assetDeclarations($config, $file);
            foreach ($declarations as $handle => $assetConfig) {
                if (!is_string($handle) || !is_array($assetConfig)) {
                    throw new RuntimeException('Invalid admin asset declaration in: ' . $file);
                }

                $asset = AdminAsset::fromConfig($handle, $assetConfig);
                if ($asset->path === '') {
                    throw new RuntimeException('Admin asset path cannot be empty for handle: ' . $handle);
                }

                if ($asset->appliesTo($screen)) {
                    $assets[] = $asset;
                }
            }
        }

        usort($assets, static fn (AdminAsset $a, AdminAsset $b): int => [$a->sortOrder, $a->handle] <=> [$b->sortOrder, $b->handle]);

        return $this->deduplicateByPhysicalPath($assets);
    }

    /**
     * Accept the canonical wrapped manifest and the established flat module
     * manifest shape used by Settings and Auth.
     *
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function assetDeclarations(array $config, string $file): array
    {
        if (array_key_exists('assets', $config)) {
            if (!is_array($config['assets'])) {
                throw new RuntimeException("Admin asset config key 'assets' must contain an array: " . $file);
            }

            return $config['assets'];
        }

        return $config;
    }

    /**
     * Load a module manifest in an isolated function scope.
     *
     * A required PHP file inherits local variables from its caller. Several
     * module manifests use `$assets` while building their return value, which
     * must not overwrite all()'s asset accumulator.
     *
     * @return mixed
     */
    private function loadConfig(string $file): mixed
    {
        return (static fn (string $manifest): mixed => require $manifest)($file);
    }

    /**
     * Return all stylesheet assets.
     *
     * @return list<AdminAsset>
     */
    public function stylesheets(?string $screen = null): array
    {
        return array_values(array_filter($this->all($screen), static fn (AdminAsset $asset): bool => $asset->type === 'style'));
    }

    /**
     * Return all script assets.
     *
     * @return list<AdminAsset>
     */
    public function scripts(?string $screen = null): array
    {
        return array_values(array_filter($this->all($screen), static fn (AdminAsset $asset): bool => $asset->type === 'script'));
    }

    /**
     * Collapse assets that resolve to the SAME physical file (ignoring any
     * cache-busting query string such as ?v=1.37l) into a single entry,
     * keeping the first occurrence in the already-sorted list.
     *
     * @param list<AdminAsset> $sortedAssets
     * @return list<AdminAsset>
     */
    private function deduplicateByPhysicalPath(array $sortedAssets): array
    {
        $seenPhysicalPaths = [];
        $deduplicated = [];

        foreach ($sortedAssets as $asset) {
            $physicalPath = strtok($asset->path, '?');
            $physicalPath = $physicalPath !== false ? $physicalPath : $asset->path;

            if (isset($seenPhysicalPaths[$physicalPath])) {
                continue;
            }

            $seenPhysicalPaths[$physicalPath] = true;
            $deduplicated[] = $asset;
        }

        return $deduplicated;
    }
}
