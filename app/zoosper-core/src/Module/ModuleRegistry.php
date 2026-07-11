<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

/**
 * Discovers Zoosper modules from core, local/project and Composer packages.
 *
 * Discovery locations:
 *
 * - app/<module>/module.php for product-owned Zoosper modules.
 * - modules/<module>/module.php for simple local modules.
 * - modules/<vendor>/<module>/module.php for project/community modules.
 * - vendor packages with composer type "zoosper-module" or extra.zoosper.module.
 *
 * Custom and marketplace modules should not edit core files. They can contribute
 * routes, services, schemas, templates and assets through their own config files.
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
        $modules = array_values(array_filter(
            $this->allModules(),
            static fn (ModuleDefinition $module): bool => $module->enabled,
        ));

        (new ModuleDependencyValidator())->validate($modules);

        return $modules;
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

        foreach ((new ComposerModuleDiscovery($this->basePath))->moduleFiles() as $moduleFile) {
            $files[] = $moduleFile;
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
