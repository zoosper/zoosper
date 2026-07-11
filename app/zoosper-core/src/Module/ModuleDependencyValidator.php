<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

use Zoosper\Core\Exception\ZoosperException;

/**
 * Validates enabled module dependencies declared through module.php metadata.
 */
final readonly class ModuleDependencyValidator
{
    /** @param list<ModuleDefinition> $modules */
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
                    throw new ZoosperException(
                        message: sprintf('Enabled module "%s" depends on missing or disabled module "%s".', $module->name, $dependency),
                        context: 'The module dependency graph is invalid. Zoosper validates dependencies before loading service providers, routes and schemas.',
                        suggestion: 'Install/enable the missing module or remove it from the depends list in `' . $module->path . '/module.php`. Then run `php tools/verify-module-dependencies.php`.',
                        docsUrl: 'docs/operations/composer-marketplace-module-development.md',
                        details: [
                            'module' => $module->name,
                            'module_path' => $module->path,
                            'missing_dependency' => $dependency,
                            'enabled_modules' => array_keys($enabledByName),
                        ],
                    );
                }
            }
        }
    }

    /** @return list<string> */
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
