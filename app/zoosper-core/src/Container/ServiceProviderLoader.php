<?php

declare(strict_types=1);

namespace Zoosper\Core\Container;

use Zoosper\Errors\ZoosperException;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Loads module-owned service provider definitions from config/services.php.
 */
final readonly class ServiceProviderLoader
{
    public function __construct(private ModuleRegistry $modules, private ServiceContainer $services)
    {
    }

    public function register(?string $basePath = null): void
    {
        if ($basePath !== null) {
            $compiledFile = rtrim($basePath, '/\\') . '/var/cache/services_compiled.php';
            if (is_file($compiledFile)) {
                $compiled = require $compiledFile;
                $this->registerDefinitions($compiled['definitions'], $compiledFile, 'aggregated_cache');
                $this->registerDecorators($compiled['decorators'], $compiledFile);
                return;
            }
        }

        foreach ($this->modules->enabledModules() as $module) {
            if (!($module->discovery['services'] ?? is_file($module->configPath('services.php')))) {
                continue;
            }

            $file = $module->configPath('services.php');
            $definitions = require $file;
            $this->registerDefinitions($definitions, $file, $module->name);
        }

        foreach ($this->modules->enabledModules() as $module) {
            if (!($module->discovery['service_decorators'] ?? is_file($module->configPath('service_decorators.php')))) {
                continue;
            }

            $file = $module->configPath('service_decorators.php');
            $decorators = require $file;
            $this->registerDecorators($decorators, $file);
        }
    }

    private function registerDefinitions(mixed $definitions, string $file, string $moduleName): void
    {
        if (!is_array($definitions)) {
            throw new ZoosperException(
                message: 'Service config must return an array: ' . $file,
                context: 'Module `' . $moduleName . '` has a services config file, but it did not return an array of service definitions.',
                suggestion: 'Update the file to return an associative array: `ServiceId::class => static fn (ServiceContainer $services): ServiceId => new ServiceId(...)`.',
                docsUrl: 'docs/operations/module-development.md',
                details: ['module' => $moduleName, 'file' => $file, 'returned_type' => get_debug_type($definitions)],
            );
        }

        foreach ($definitions as $id => $definition) {
            if (!is_string($id) || $id === '') {
                throw new ZoosperException(
                    message: 'Service config has an invalid service ID in: ' . $file,
                    context: 'Every service definition key must be a non-empty string. Class names or named string IDs are valid.',
                    suggestion: 'Use a class-string key such as `MailerInterface::class` or a named ID such as `theme.frontend_template_renderer`.',
                    docsUrl: 'docs/operations/module-development.md',
                    details: ['module' => $moduleName, 'file' => $file, 'service_id_type' => get_debug_type($id)],
                );
            }

            if (is_object($definition) && !is_callable($definition)) {
                $this->services->set($id, $definition);
                continue;
            }

            if (!is_callable($definition)) {
                throw new ZoosperException(
                    message: 'Service definition must be an object or callable for: ' . $id,
                    context: 'Module `' . $moduleName . '` declared a service definition that is neither an object instance nor a callable factory.',
                    suggestion: 'Return a callable factory: `' . $id . ' => static fn (ServiceContainer $services): object => new YourService(...)`.',
                    docsUrl: 'docs/operations/module-development.md',
                    details: ['module' => $moduleName, 'file' => $file, 'service_id' => $id, 'definition_type' => get_debug_type($definition)],
                );
            }

            $this->services->factory($id, static function (ServiceContainer $services) use ($definition, $id, $file, $moduleName): object {
                $service = $definition($services);
                if (!is_object($service)) {
                    throw new ZoosperException(
                        message: 'Service factory did not return an object for: ' . $id,
                        context: 'The service factory for `' . $id . '` (from ' . $file . ') executed, but returned a non-object value.',
                        suggestion: 'Change the factory to return an object instance. Then run `php tools/verify-service-providers.php`.',
                        docsUrl: 'docs/operations/module-development.md',
                        details: ['module' => $moduleName, 'file' => $file, 'service_id' => $id, 'returned_type' => get_debug_type($service)],
                    );
                }

                return $service;
            });
        }
    }

    private function registerDecorators(mixed $decorators, string $file): void
    {
        if (!is_array($decorators)) {
            throw new ZoosperException(message: 'Service decorators must return an array.', context: $file);
        }
        foreach ($decorators as $id => $decorator) {
            if (!is_string($id) || !is_callable($decorator)) {
                throw new ZoosperException(message: 'Invalid service decorator declaration.', context: $file);
            }
            $this->services->decorate($id, $decorator);
        }
    }
}










