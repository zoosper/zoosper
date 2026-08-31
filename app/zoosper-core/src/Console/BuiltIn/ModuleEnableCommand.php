<?php

declare(strict_types=1);

namespace Zoosper\Core\Console\BuiltIn;

use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Module\ModuleRepository;
use Zoosper\Core\Module\ModuleManifestCompiler;

/**
 * Enables a module in the database and re-compiles the manifest.
 */
final readonly class ModuleEnableCommand implements ConsoleCommandInterface
{
    public function __construct(
        private ModuleRepository $repository,
        private ModuleManifestCompiler $compiler
    ) {
    }

    public function name(): string
    {
        return 'module:enable';
    }

    public function description(): string
    {
        return 'Enable a Zoosper module.';
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        $name = $args[0] ?? null;
        if ($name === null) {
            $output->errorln('Module name is required.');
            return 1;
        }

        $this->repository->setStatus($name, 'enabled');
        $output->writeln("Module '{$name}' enabled in database.");
        
        $output->writeln('Re-compiling module manifest...');
        $this->compiler->compile();
        
        $output->writeln('Done.');
        return 0;
    }
}
