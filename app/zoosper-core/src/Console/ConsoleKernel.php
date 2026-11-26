<?php
declare(strict_types=1);
namespace Zoosper\Core\Console;
use Zoosper\Core\Console\BuiltIn\CacheClearCommand;
use Zoosper\Core\Console\BuiltIn\CompileCommand;
use Zoosper\Core\Console\BuiltIn\ManifestCheckCommand;
use Zoosper\Core\Console\BuiltIn\ManifestStatusCommand;
use Zoosper\Core\Console\BuiltIn\MigrateCommand;
use Zoosper\Core\Module\ModuleRegistry;
final readonly class ConsoleKernel
{
    public function __construct(private ConsoleServiceFactory $services, private array $builtIns) {}
    public function application(): ConsoleApplication
    {
        $container = $this->services->create();
        $commands = [];
        foreach ($this->builtIns as $command) { $commands[$command->name()] = $command; }
        $modules = $container->get(ModuleRegistry::class);
        if (!$modules instanceof ModuleRegistry) {
            throw new \TypeError('Service must be a ModuleRegistry, ' . get_debug_type($modules) . ' given.');
        }
        foreach ((new ModuleConsoleCommandLoader($modules, $container))->load() as $name => $command) { $commands[$name] = $command; }
        return new ConsoleApplication([], $commands);
    }
}










