<?php

declare(strict_types=1);

namespace Zoosper\Core\Console\BuiltIn;

use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Module\ModuleRepository;
use Zoosper\Core\Module\ModuleManifestCompiler;

/**
 * Uninstalls a module by disabling it and marking as uninstalled.
 */
final readonly class ModuleUninstallCommand implements ConsoleCommandInterface
{
    public function __construct(
        private ModuleRepository $repository,
        private ModuleManifestCompiler $compiler
    ) {
    }

    public function name(): string
    {
        return 'module:uninstall';
    }

    public function description(): string
    {
        return 'Uninstall a Zoosper module (disables and marks as uninstalled).';
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        $name = $args[0] ?? null;
        if ($name === null) {
            $output->errorln('Module name is required.');
            return 1;
        }

        $output->writeln("Uninstalling module '{$name}'...");
        
        $this->repository->setStatus($name, 'uninstalled');
        
        $output->writeln('Re-compiling module manifest...');
        $this->compiler->compile();
        
        $output->writeln("Module '{$name}' uninstalled successfully. Note: Database tables were NOT dropped.");
        return 0;
    }
}
