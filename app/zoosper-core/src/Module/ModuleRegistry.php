<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

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

        foreach ($this->moduleRoots() as $root) {
            foreach (glob($root . '/*/module.php') ?: [] as $moduleFile) {
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
        }

        usort(
            $modules,
            static fn (ModuleDefinition $a, ModuleDefinition $b): int => $a->name <=> $b->name,
        );

        return $modules;
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
