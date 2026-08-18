<?php
declare(strict_types=1);
namespace Zoosper\Logger\Module;
use Zoosper\Logger\Manager\LogManager;
final readonly class ModuleLoggerProviderLoader
{
    public function __construct(private object $modules, private LogManager $logs, private object $services) {}
    public function register(): void
    {
        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('logging.php');
            if (!is_file($file)) continue;
            $config = require $file;
            if (!is_array($config)) throw new \RuntimeException('Logging config must return an array: ' . $file);
            $name = (string) $module->name;
            $configuredFile = $config['file'] ?? null;
            $logger = is_string($configuredFile) && $configuredFile !== '' ? $this->logs->forFile($configuredFile) : $this->logs->module($name);
            $this->services->set('logger.' . $name, $logger);
        }
    }
}
