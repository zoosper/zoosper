<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

use RuntimeException;

/**
 * Validates enabled module dependencies declared through module.php metadata.
 *
 * Modules may declare:
 *
 * ```php
 * 'depends' => ['zoosper-core', 'zoosper-page']
 * ```
 *
 * Dependency validation is intentionally module-name based for the foundation
 * phase. Future phases may add version constraints and conflict declarations.
 */
final readonly class ModuleDependencyValidator
{
    /**
     * Validate dependencies for enabled modules.
     *
     * @param list<ModuleDefinition> $modules
     */
    public function validate(array $modules): void
    {
        $enabledByName = [];
        foreach ($modules as $module) {
            if ($module->enabled) {
                $enabledByName[$module->name] = true;
            }
        }

        foreach ($modules as $module) {
            if (!$module->enabled) {
                continue;
            }

            foreach ($this->depends($module) as $dependency) {
                if (!isset($enabledByName[$dependency])) {
                    throw new RuntimeException(sprintf(
                        'Enabled module "%s" depends on missing or disabled module "%s".',
                        $module->name,
                        $dependency,
                    ));
                }
            }
        }
    }

    /**
     * @return list<string>
     */
    public function depends(ModuleDefinition $module): array
    {
        $depends = $module->metadata['depends'] ?? [];
        if (!is_array($depends)) {
            return [];
        }

        $normalised = [];
        foreach ($depends as $dependency) {
            if (is_string($dependency) && trim($dependency) !== '') {
                $normalised[] = trim($dependency);
            }
        }

        return array_values(array_unique($normalised));
    }
}
