<?php

declare(strict_types=1);

namespace Zoosper\Core\Console;

/**
 * Contract for module-owned CLI commands.
 *
 * Modules register command classes in config/console.php. Commands that need
 * dependencies should also be registered in config/services.php so the command
 * loader can resolve them from the service container.
 */
interface ConsoleCommandInterface
{
    public function name(): string;

    public function description(): string;

    /**
     * Execute the command.
     *
     * @param list<string> $args Raw CLI arguments after the command name.
     */
    public function run(array $args, ConsoleOutput $output): int;
}
