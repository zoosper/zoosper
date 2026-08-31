<?php

declare(strict_types=1);

namespace Zoosper\Core\Asset;

use Zoosper\Core\Module\ModuleRegistry;

/**
 * Discovers module-owned `config/assets.php` manifests and registers each
 * module's asset directory into an AssetModuleRegistry.
 *
 * This mirrors the config-discovery approach used by ServiceProviderLoader and
 * ModuleEventListenerLoader: a dropped-in module simply ships a config/assets.php
 * and its assets become servable — no core edits, no copying into public/.
 *
 * A module's config/assets.php must return a map of:
 *     logical module name (string) => absolute assets directory (string)
 */
final readonly class ModuleAssetManifestLoader
{
    public function __construct(
        private ModuleRegistry $modules,
    ) {
    }

    /**
     * Walk every enabled module, require its config/assets.php when present, and
     * merge its entries into the given registry.
     */
    public function registerInto(AssetModuleRegistry $registry): void
    {
        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('assets.php');
            if (!is_file($file)) {
                continue;
            }

            $definitions = require $file;

            self::mergeDefinitions($registry, $definitions, $file);
        }
    }

    /**
     * Merge a single manifest's definitions into the registry.
     *
     * Kept static and dependency-free so it can be unit-tested directly with
     * fixture arrays, independent of the module system.
     *
     * @param mixed  $definitions The value returned by a config/assets.php file.
     * @param string $sourceFile  For error context.
     *
     * @return list<string> The logical module names that were registered.
     */
    public static function mergeDefinitions(
        AssetModuleRegistry $registry,
        mixed $definitions,
        string $sourceFile = '(unknown)',
    ): array {
        if (!is_array($definitions)) {
            throw new \InvalidArgumentException(
                "Asset manifest must return an array of 'module' => 'assets dir': {$sourceFile}"
            );
        }

        $registered = [];

        foreach ($definitions as $name => $dir) {
            if (!is_string($name) || $name === '') {
                throw new \InvalidArgumentException(
                    "Asset manifest has an invalid module name in: {$sourceFile}"
                );
            }
            if (!is_string($dir) || $dir === '') {
                throw new \InvalidArgumentException(
                    "Asset manifest entry '{$name}' must map to a non-empty directory string in: {$sourceFile}"
                );
            }

            $registry->register($name, $dir);
            $registered[] = $name;
        }

        return $registered;
    }
}










