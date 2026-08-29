<?php

declare(strict_types=1);

namespace Zoosper\Admin\Theme;

use RuntimeException;
use Zoosper\Core\Module\ModuleRegistry;

/** Discovers trusted Admin colour-palette metadata from enabled modules. */
final readonly class ModuleAdminColourThemeLoader
{
    public function __construct(private ModuleRegistry $modules)
    {
    }

    /** @return list<AdminColourTheme> */
    public function all(): array
    {
        $themes = [];

        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('admin_colour_themes.php');
            if (!is_file($file)) {
                continue;
            }

            $config = (static fn (string $manifest): mixed => require $manifest)($file);
            if (!is_array($config) || !is_array($config['themes'] ?? null)) {
                throw new RuntimeException("Admin colour theme config key 'themes' must contain an array: " . $file);
            }

            foreach ($config['themes'] as $code => $declaration) {
                if (!is_string($code) || !is_array($declaration)) {
                    throw new RuntimeException('Invalid Admin colour theme declaration in: ' . $file);
                }

                if (isset($themes[$code])) {
                    throw new RuntimeException('Duplicate Admin colour theme code: ' . $code);
                }

                $themes[$code] = AdminColourTheme::fromConfig($code, $declaration);
            }
        }

        foreach (['light', 'dark'] as $required) {
            if (!isset($themes[$required])) {
                throw new RuntimeException('Required Admin colour theme is not registered: ' . $required);
            }
        }

        $themes = array_values($themes);
        usort(
            $themes,
            static fn (AdminColourTheme $left, AdminColourTheme $right): int =>
                [$left->sortOrder, $left->name, $left->code] <=> [$right->sortOrder, $right->name, $right->code],
        );

        return $themes;
    }
}
