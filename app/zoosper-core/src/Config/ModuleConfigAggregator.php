<?php

declare(strict_types=1);

namespace Zoosper\Core\Config;

use Zoosper\Core\Module\ModuleRegistry;

/**
 * Aggregates layered application configuration.
 *
 * Priority (low -> high):
 *   1. Each enabled module config/settings/*.php (module-shipped defaults)
 *   2. The project root config/*.php (always wins)
 *
 * Module application-config lives under config/settings/ so it never collides
 * with reserved module-system files (services.php, controllers.php,
 * db_schema.php, events.php, logging.php, admin_menu.php, acl.php, ...).
 *
 * PCI-aware: configuration must never contain secrets. Secrets belong in .env
 * and are read through env() inside config files, not stored as literal values.
 */
final readonly class ModuleConfigAggregator
{
    public function __construct(
        private ModuleRegistry $modules,
        private string $rootConfigPath,
    ) {
    }

    /** @return array<string, mixed> */
    public function aggregate(): array
    {
        $directories = [];

        foreach ($this->modules->enabledModules() as $module) {
            $directories[] = $module->configPath('settings');
        }

        // Root config has the highest priority, so it is merged last.
        $directories[] = rtrim($this->rootConfigPath, '/');

        return self::fromDirectories($directories);
    }

    /**
     * @param list<string> $directoriesLowToHigh
     * @return array<string, mixed>
     */
    public static function fromDirectories(array $directoriesLowToHigh): array
    {
        $items = [];

        foreach ($directoriesLowToHigh as $directory) {
            foreach (glob(rtrim($directory, '/') . '/*.php') ?: [] as $file) {
                $key = basename($file, '.php');
                $value = require $file;

                if (!is_array($value)) {
                    continue;
                }

                if (array_key_exists($key, $items) && is_array($items[$key])) {
                    $items[$key] = self::mergeConfig($items[$key], $value);
                } else {
                    $items[$key] = $value;
                }
            }
        }

        return $items;
    }

    /**
     * Deep-merge two config arrays; $high overrides $low. Associative arrays
     * merge recursively; lists and scalars are replaced wholesale.
     *
     * @param array<int|string, mixed> $low
     * @param array<int|string, mixed> $high
     * @return array<int|string, mixed>
     */
    public static function mergeConfig(array $low, array $high): array
    {
        foreach ($high as $key => $value) {
            if (
                is_string($key)
                && isset($low[$key])
                && is_array($low[$key])
                && is_array($value)
                && self::isAssociative($low[$key])
                && self::isAssociative($value)
            ) {
                $low[$key] = self::mergeConfig($low[$key], $value);
            } else {
                $low[$key] = $value;
            }
        }

        return $low;
    }

    /** @param array<int|string, mixed> $array */
    private static function isAssociative(array $array): bool
    {
        return $array !== [] && array_keys($array) !== range(0, count($array) - 1);
    }
}