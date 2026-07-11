<?php

declare(strict_types=1);

namespace Zoosper\Core\Container;

use RuntimeException;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Loads module-owned service provider definitions from config/services.php.
 *
 * A services.php file returns an associative array where keys are service IDs
 * and values are either objects or callables that accept ServiceContainer and
 * return an object. Later modules may override earlier service IDs, which lets
 * custom marketplace/local modules replace core behaviour without editing core
 * bootstrap files.
 */
final readonly class ServiceProviderLoader
{
    public function __construct(private ModuleRegistry $modules, private ServiceContainer $services)
    {
    }

    /**
     * Register all services declared by enabled modules.
     */
    public function register(): void
    {
        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('services.php');
            if (!is_file($file)) {
                continue;
            }

            $definitions = require $file;
            if (!is_array($definitions)) {
                throw new RuntimeException('Service config must return an array: ' . $file);
            }

            foreach ($definitions as $id => $definition) {
                if (!is_string($id) || $id === '') {
                    throw new RuntimeException('Service config has invalid service ID in: ' . $file);
                }

                if (is_object($definition) && !is_callable($definition)) {
                    $this->services->set($id, $definition);
                    continue;
                }

                if (!is_callable($definition)) {
                    throw new RuntimeException('Service definition must be an object or callable for: ' . $id . ' in ' . $file);
                }

                $this->services->factory($id, static function (ServiceContainer $services) use ($definition, $id, $file): object {
                    $service = $definition($services);
                    if (!is_object($service)) {
                        throw new RuntimeException('Service factory did not return an object for: ' . $id . ' in ' . $file);
                    }

                    return $service;
                });
            }
        }
    }
}
