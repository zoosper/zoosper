<?php

declare(strict_types=1);

namespace Zoosper\Core\Console\BuiltIn;

use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Module\ModuleRepository;
use Zoosper\Core\Module\ModuleManifestCompiler;

/**
 * Disables a module in the database and re-compiles the manifest.
 */
final readonly class ModuleDisableCommand implements ConsoleCommandInterface
{
    public function __construct(
        private ModuleRepository $repository,
        private ModuleManifestCompiler $compiler
    ) {
    }

    public function name(): string
    {
        return 'module:disable';
    }

    public function description(): string
    {
        return 'Disable a Zoosper module.';
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        $name = $args[0] ?? null;
        if ($name === null) {
            $output->errorln('Module name is required.');
            return 1;
        }

        $this->repository->setStatus($name, 'disabled');
        $output->writeln("Module '{$name}' disabled in database.");
        
        $output->writeln('Re-compiling module manifest...');
        $this->compiler->compile();
        
        $output->writeln('Done.');
        return 0;
    }
}
