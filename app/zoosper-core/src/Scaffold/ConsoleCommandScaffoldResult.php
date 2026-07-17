<?php

declare(strict_types=1);

namespace Zoosper\Core\Scaffold;

/**
 * Result returned after scaffolding a module-owned console command.
 */
final readonly class ConsoleCommandScaffoldResult
{
    /** @param list<string> $createdFiles */
    public function __construct(
        public string $moduleName,
        public string $commandClass,
        public string $commandName,
        public string $commandFile,
        public string $consoleConfigFile,
        public array $createdFiles,
    ) {
    }
}
