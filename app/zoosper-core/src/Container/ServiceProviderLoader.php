<?php

declare(strict_types=1);

namespace Zoosper\Core\Container;

use Zoosper\Core\Exception\ZoosperException;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Loads module-owned service provider definitions from config/services.php.
 */
final readonly class ServiceProviderLoader
{
    public function __construct(private ModuleRegistry $modules, private ServiceContainer $services)
    {
    }

    public function register(): void
    {
        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('services.php');
            if (!is_file($file)) {
                continue;
            }

            $definitions = require $file;
            if (!is_array($definitions)) {
                throw new ZoosperException(
                    message: 'Service config must return an array: ' . $file,
                    context: 'Module `' . $module->name . '` has a config/services.php file, but it did not return an array of service definitions.',
                    suggestion: 'Update the file to return an associative array: `ServiceId::class => static fn (ServiceContainer $services): ServiceId => new ServiceId(...)`.',
                    docsUrl: 'docs/operations/module-development.md',
                    details: ['module' => $module->name, 'file' => $file, 'returned_type' => get_debug_type($definitions)],
                );
            }

            foreach ($definitions as $id => $definition) {
                if (!is_string($id) || $id === '') {
                    throw new ZoosperException(
                        message: 'Service config has an invalid service ID in: ' . $file,
                        context: 'Every service definition key must be a non-empty string. Class names or named string IDs are valid.',
                        suggestion: 'Use a class-string key such as `MailerInterface::class` or a named ID such as `theme.frontend_template_renderer`.',
                        docsUrl: 'docs/operations/module-development.md',
                        details: ['module' => $module->name, 'file' => $file, 'service_id_type' => get_debug_type($id)],
                    );
                }

                if (is_object($definition) && !is_callable($definition)) {
                    $this->services->set($id, $definition);
                    continue;
                }

                if (!is_callable($definition)) {
                    throw new ZoosperException(
                        message: 'Service definition must be an object or callable for: ' . $id,
                        context: 'Module `' . $module->name . '` declared a service definition that is neither an object instance nor a callable factory.',
                        suggestion: 'Return a callable factory: `' . $id . ' => static fn (ServiceContainer $services): object => new YourService(...)`.',
                        docsUrl: 'docs/operations/module-development.md',
                        details: ['module' => $module->name, 'file' => $file, 'service_id' => $id, 'definition_type' => get_debug_type($definition)],
                    );
                }

                $this->services->factory($id, static function (ServiceContainer $services) use ($definition, $id, $file, $module): object {
                    $service = $definition($services);
                    if (!is_object($service)) {
                        throw new ZoosperException(
                            message: 'Service factory did not return an object for: ' . $id,
                            context: 'The service factory in `' . $file . '` executed, but returned a non-object value.',
                            suggestion: 'Change the factory to return an object instance. Then run `php tools/verify-service-providers.php`.',
                            docsUrl: 'docs/operations/module-development.md',
                            details: ['module' => $module->name, 'file' => $file, 'service_id' => $id, 'returned_type' => get_debug_type($service)],
                        );
                    }

                    return $service;
                });
            }
        }
    }
}
