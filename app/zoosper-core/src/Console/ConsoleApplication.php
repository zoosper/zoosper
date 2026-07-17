<?php

declare(strict_types=1);

namespace Zoosper\Core\Console;

/**
 * Lightweight command router for bin/zoosper.
 *
 * Built-in commands remain implemented by the bin/zoosper entry point for this
 * foundation phase. Module-owned commands are routed through this application.
 */
final readonly class ConsoleApplication
{
    /**
     * @param list<string> $builtInCommands
     * @param array<string, ConsoleCommandInterface> $moduleCommands
     */
    public function __construct(
        private array $builtInCommands,
        private array $moduleCommands = [],
    ) {
    }

    /** @param list<string> $args */
    public function run(string $commandName, array $args, ConsoleOutput $output): int
    {
        if ($commandName === 'help' || $commandName === 'list') {
            $this->writeHelp($output);
            return 0;
        }

        if (isset($this->moduleCommands[$commandName])) {
            return $this->moduleCommands[$commandName]->run($args, $output);
        }

        $output->errorln('Unknown command: ' . $commandName);
        $output->errorln();
        $this->writeHelp($output);

        return 1;
    }

    public function writeHelp(ConsoleOutput $output): void
    {
        $output->writeln('Zoosper CLI');
        $output->writeln('Commands:');

        foreach ($this->builtInCommands as $command) {
            $output->writeln('  ' . $command);
        }

        if ($this->moduleCommands === []) {
            return;
        }

        $output->writeln('');
        $output->writeln('Module commands:');

        $commands = $this->moduleCommands;
        ksort($commands);
        foreach ($commands as $command) {
            $description = trim($command->description());
            $output->writeln('  ' . $command->name() . ($description !== '' ? '  ' . $description : ''));
        }
    }
}
