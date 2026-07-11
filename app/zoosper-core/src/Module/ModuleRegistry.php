<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

/**
 * Discovers Zoosper modules from core app modules and custom/community modules.
 *
 * Core modules live under `app/zoosper-*`. Project-specific or marketplace-style
 * modules should live under `modules/<module>/module.php` or
 * `modules/<vendor>/<module>/module.php`. Custom modules can override core
 * service IDs by using a higher module `sort_order` in module.php and declaring
 * matching service IDs in config/services.php.
 */
final readonly class ModuleRegistry
{
    public function __construct(private string $basePath)
    {
    }

    /**
     * @return list<ModuleDefinition>
     */
    public function enabledModules(): array
    {
        return array_values(array_filter(
            $this->allModules(),
            static fn (ModuleDefinition $module): bool => $module->enabled,
        ));
    }

    /**
     * @return list<ModuleDefinition>
     */
    public function allModules(): array
    {
        $modules = [];

        foreach ($this->moduleFiles() as $moduleFile) {
            $metadata = require $moduleFile;

            if (!is_array($metadata)) {
                continue;
            }

            $name = (string) ($metadata['name'] ?? basename(dirname($moduleFile)));
            $modules[] = new ModuleDefinition(
                name: $name,
                path: dirname($moduleFile),
                enabled: (bool) ($metadata['enabled'] ?? true),
                metadata: $metadata,
            );
        }

        usort($modules, static function (ModuleDefinition $a, ModuleDefinition $b): int {
            $aOrder = (int) ($a->metadata['sort_order'] ?? 100);
            $bOrder = (int) ($b->metadata['sort_order'] ?? 100);

            return $aOrder <=> $bOrder ?: $a->name <=> $b->name;
        });

        return $modules;
    }

    /**
     * @return list<string>
     */
    private function moduleFiles(): array
    {
        $files = [];

        foreach ($this->moduleRoots() as $root) {
            foreach (glob($root . '/*/module.php') ?: [] as $moduleFile) {
                $files[] = $moduleFile;
            }

            foreach (glob($root . '/*/*/module.php') ?: [] as $moduleFile) {
                $files[] = $moduleFile;
            }
        }

        $files = array_values(array_unique($files));
        sort($files);

        return $files;
    }

    /**
     * @return list<string>
     */
    private function moduleRoots(): array
    {
        return array_values(array_filter([
            $this->basePath . '/app',
            $this->basePath . '/modules',
        ], static fn (string $path): bool => is_dir($path)));
    }
}
