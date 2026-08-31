<?php

declare(strict_types=1);

namespace Zoosper\Core\Console\BuiltIn;

use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Module\ModuleRepository;
use Zoosper\Core\Module\ModuleManifestCompiler;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Database\Migrator;

/**
 * Installs a module by enabling it and running migrations.
 */
final readonly class ModuleInstallCommand implements ConsoleCommandInterface
{
    public function __construct(
        private ModuleRepository $repository,
        private ModuleManifestCompiler $compiler,
        private ModuleRegistry $registry,
        private Migrator $migrator
    ) {
    }

    public function name(): string
    {
        return 'module:install';
    }

    public function description(): string
    {
        return 'Install a Zoosper module and run its migrations.';
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        $name = $args[0] ?? null;
        if ($name === null) {
            $output->errorln('Module name is required.');
            return 1;
        }

        $modules = $this->registry->discoverModulesLive();
        $target = null;
        foreach ($modules as $m) {
            if ($m->name === $name) {
                $target = $m;
                break;
            }
        }

        if ($target === null) {
            $output->errorln("Module '{$name}' not found on disk.");
            return 1;
        }

        $output->writeln("Installing module '{$name}'...");
        
        $this->repository->markInstalled($name);
        
        $output->writeln('Re-compiling module manifest...');
        $this->compiler->compile();
        
        $output->writeln('Running migrations...');
        $this->migrator->migrate();
        
        $output->writeln("Module '{$name}' installed successfully.");
        return 0;
    }
}










