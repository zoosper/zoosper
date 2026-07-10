<?php

declare(strict_types=1);

namespace Zoosper\Core\Log;

use RuntimeException;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Module\ModuleRegistry;

final readonly class ModuleLoggerProviderLoader
{
    public function __construct(
        private ModuleRegistry $modules,
        private LogManager $logManager,
        private ServiceContainer $services,
    ) {
    }

    public function register(): void
    {
        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('logging.php');
            if (!is_file($file)) {
                continue;
            }

            $config = require $file;
            if (!is_array($config)) {
                throw new RuntimeException('Logging config must return an array: ' . $file);
            }

            $serviceId = (string) ($config['service'] ?? 'logger.' . $module->name);
            $logFile = (string) ($config['file'] ?? $module->name . '.log');

            $this->services->set($serviceId, $this->logManager->forFile($logFile));
        }
    }
}
