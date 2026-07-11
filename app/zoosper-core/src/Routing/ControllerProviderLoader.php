<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

use Throwable;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Exception\ZoosperException;
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
                throw new ZoosperException(
                    message: 'Controller config must return an array: ' . $file,
                    context: 'Module `' . $module->name . '` has a config/controllers.php file that did not return an array.',
                    suggestion: 'Return an array keyed by controller class name with callable factories as values.',
                    docsUrl: 'docs/operations/troubleshooting-helpful-errors.md',
                    details: ['module' => $module->name, 'file' => $file, 'returned_type' => get_debug_type($config)],
                );
            }

            foreach ($config as $class => $factory) {
                if (!is_string($class) || $class === '') {
                    throw new ZoosperException(
                        message: 'Controller config has an invalid class key in: ' . $file,
                        context: 'Controller config keys must be class-string names.',
                        suggestion: 'Use `SomeController::class => static fn (ServiceContainer $services): SomeController => new SomeController(...)`.',
                        docsUrl: 'docs/operations/troubleshooting-helpful-errors.md',
                        details: ['module' => $module->name, 'file' => $file, 'class_key_type' => get_debug_type($class)],
                    );
                }

                if (!is_callable($factory)) {
                    throw new ZoosperException(
                        message: 'Controller factory must be callable for: ' . $class,
                        context: 'The controller provider did not declare a callable factory for controller `' . $class . '`.',
                        suggestion: 'Update `' . $file . '` so the controller maps to a callable factory receiving ServiceContainer.',
                        docsUrl: 'docs/operations/troubleshooting-helpful-errors.md',
                        details: ['module' => $module->name, 'file' => $file, 'controller' => $class, 'factory_type' => get_debug_type($factory)],
                    );
                }

                try {
                    $controller = $factory($this->services);
                } catch (Throwable $exception) {
                    throw new ZoosperException(
                        message: 'Controller factory failed for: ' . $class,
                        context: 'Zoosper was loading module `' . $module->name . '` controller providers from `' . $file . '`.',
                        suggestion: 'Check the controller constructor dependencies and registered services. Then run `php tools/verify-service-providers.php`.',
                        docsUrl: 'docs/operations/troubleshooting-helpful-errors.md',
                        details: ['module' => $module->name, 'file' => $file, 'controller' => $class],
                        previous: $exception,
                    );
                }

                if (!is_object($controller)) {
                    throw new ZoosperException(
                        message: 'Controller factory did not return an object for: ' . $class,
                        context: 'Controller factories must return controller object instances.',
                        suggestion: 'Return `new ' . $class . '(...)` from the factory.',
                        docsUrl: 'docs/operations/troubleshooting-helpful-errors.md',
                        details: ['module' => $module->name, 'file' => $file, 'controller' => $class, 'returned_type' => get_debug_type($controller)],
                    );
                }

                $controllers[$class] = $controller;
            }
        }

        return $controllers;
    }
}
