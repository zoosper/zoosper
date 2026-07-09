<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

use RuntimeException;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Module\ModuleRegistry;

final readonly class ControllerProviderLoader
{
    public function __construct(private ModuleRegistry $modules, private ServiceContainer $services)
    {
    }

    /** @return array<class-string, object> */
    public function load(): array
    {
        $controllers = [];

        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('controllers.php');
            if (!is_file($file)) {
                continue;
            }

            $config = require $file;
            if (!is_array($config)) {
                throw new RuntimeException('Controller config must return an array: ' . $file);
            }

            foreach ($config as $class => $factory) {
                if (!is_string($class) || $class === '') {
                    throw new RuntimeException('Controller config has invalid class key in: ' . $file);
                }
                if (!is_callable($factory)) {
                    throw new RuntimeException('Controller factory must be callable for: ' . $class);
                }
                $controller = $factory($this->services);
                if (!is_object($controller)) {
                    throw new RuntimeException('Controller factory did not return an object for: ' . $class);
                }
                $controllers[$class] = $controller;
            }
        }

        return $controllers;
    }
}
