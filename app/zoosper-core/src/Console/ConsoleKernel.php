<?php
declare(strict_types=1);
namespace Zoosper\Core\Console;
use Zoosper\Core\Console\BuiltIn\CacheClearCommand;
use Zoosper\Core\Console\BuiltIn\CompileCommand;
use Zoosper\Core\Console\BuiltIn\ManifestCheckCommand;
use Zoosper\Core\Console\BuiltIn\ManifestStatusCommand;
use Zoosper\Core\Console\BuiltIn\MigrateCommand;
final readonly class ConsoleKernel
{
    public function __construct(private ConsoleServiceFactory $services, private array $builtIns) {}
    public function application(): ConsoleApplication
    {
        $container = $this->services->create();
        $commands = [];
        foreach ($this->builtIns as $command) { $commands[$command->name()] = $command; }
        foreach ((new ModuleConsoleCommandLoader($container->get(\Zoosper\Core\Module\ModuleRegistry::class), $container))->load() as $name => $command) { $commands[$name] = $command; }
        return new ConsoleApplication([], $commands);
    }
}
