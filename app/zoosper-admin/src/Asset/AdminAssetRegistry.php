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
 */
final readonly class AdminAssetRegistry
{
    public function __construct(private ModuleRegistry $modules)
    {
    }

    /**
     * Return all enabled admin assets sorted by sort order and handle.
     *
     * @return list<AdminAsset>
     */
    public function all(): array
    {
        $assets = [];

        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('admin_assets.php');
            if (!is_file($file)) {
                continue;
            }

            $config = require $file;
            if (!is_array($config)) {
                throw new RuntimeException('Admin asset config must return an array: ' . $file);
            }

            foreach (($config['assets'] ?? []) as $handle => $assetConfig) {
                if (!is_string($handle) || !is_array($assetConfig)) {
                    throw new RuntimeException('Invalid admin asset declaration in: ' . $file);
                }

                $asset = AdminAsset::fromConfig($handle, $assetConfig);
                if ($asset->path === '') {
                    throw new RuntimeException('Admin asset path cannot be empty for handle: ' . $handle);
                }

                $assets[] = $asset;
            }
        }

        usort($assets, static fn (AdminAsset $a, AdminAsset $b): int => [$a->sortOrder, $a->handle] <=> [$b->sortOrder, $b->handle]);

        return $assets;
    }

    /**
     * Return all stylesheet assets.
     *
     * @return list<AdminAsset>
     */
    public function stylesheets(): array
    {
        return array_values(array_filter($this->all(), static fn (AdminAsset $asset): bool => $asset->type === 'style'));
    }

    /**
     * Return all script assets.
     *
     * @return list<AdminAsset>
     */
    public function scripts(): array
    {
        return array_values(array_filter($this->all(), static fn (AdminAsset $asset): bool => $asset->type === 'script'));
    }
}
