<?php

declare(strict_types=1);

namespace Zoosper\Core\Console;

use ReflectionClass;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Exception\ZoosperException;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Discovers module-owned console commands from config/console.php.
 *
 * A module contributes commands by returning a list of command class strings:
 *
 * return [Acme\Blog\Console\ReindexCommand::class];
 *
 * Commands with dependencies should be registered in config/services.php and the
 * loader will resolve them through ServiceContainer. Commands without required
 * constructor arguments may be instantiated directly.
 */
final readonly class ModuleConsoleCommandLoader
{
    public function __construct(private ModuleRegistry $modules, private ServiceContainer $services)
    {
    }

    /** @return array<string, ConsoleCommandInterface> keyed by command name */
    public function load(): array
    {
        $commands = [];

        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('console.php');
            if (!is_file($file)) {
                continue;
            }

            $config = require $file;
            if (!is_array($config)) {
                throw new ZoosperException(
                    message: 'Console command config must return an array: ' . $file,
                    context: 'Module `' . $module->name . '` config/console.php did not return a list of command class strings.',
                    suggestion: 'Return a list such as [MyCommand::class]. Register commands with dependencies in config/services.php.',
                    docsUrl: 'docs/contributor/module-console-commands.md',
                    details: ['module' => $module->name, 'file' => $file, 'returned_type' => get_debug_type($config)],
                );
            }

            foreach ($config as $entry) {
                $command = $this->resolveCommand($entry, $module->name, $file);
                $name = trim($command->name());
                if ($name === '') {
                    throw new ZoosperException(
                        message: 'Console command name cannot be empty: ' . $command::class,
                        context: 'A command contributed by module `' . $module->name . '` returned an empty name().',
                        suggestion: 'Return a stable command name such as `vendor:task:run` from the command name() method.',
                        docsUrl: 'docs/contributor/module-console-commands.md',
                        details: ['module' => $module->name, 'file' => $file, 'command' => $command::class],
                    );
                }

                if (isset($commands[$name])) {
                    throw new ZoosperException(
                        message: 'Duplicate console command name: ' . $name,
                        context: 'More than one enabled module contributed the same console command name.',
                        suggestion: 'Use a vendor/module-prefixed command name, for example `blog:posts:reindex`.',
                        docsUrl: 'docs/contributor/module-console-commands.md',
                        details: ['module' => $module->name, 'file' => $file, 'command_name' => $name],
                    );
                }

                $commands[$name] = $command;
            }
        }

        ksort($commands);

        return $commands;
    }

    private function resolveCommand(mixed $entry, string $moduleName, string $file): ConsoleCommandInterface
    {
        if ($entry instanceof ConsoleCommandInterface) {
            return $entry;
        }

        if (!is_string($entry) || trim($entry) === '') {
            throw new ZoosperException(
                message: 'Invalid console command entry in: ' . $file,
                context: 'Each config/console.php entry must be a command class string or ConsoleCommandInterface instance.',
                suggestion: 'Use [MyCommand::class].',
                docsUrl: 'docs/contributor/module-console-commands.md',
                details: ['module' => $moduleName, 'file' => $file, 'entry_type' => get_debug_type($entry)],
            );
        }

        if ($this->services->has($entry)) {
            $command = $this->services->get($entry);
        } else {
            if (!class_exists($entry)) {
                throw new ZoosperException(
                    message: 'Console command class does not exist: ' . $entry,
                    context: 'A module declared a console command class that is not autoloadable.',
                    suggestion: 'Check the class namespace, composer autoload mapping, and config/console.php entry. Then run `composer dump-autoload`.',
                    docsUrl: 'docs/contributor/module-console-commands.md',
                    details: ['module' => $moduleName, 'file' => $file, 'command' => $entry],
                );
            }

            $reflection = new ReflectionClass($entry);
            $constructor = $reflection->getConstructor();
            if ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) {
                throw new ZoosperException(
                    message: 'Console command requires dependencies and is not registered as a service: ' . $entry,
                    context: 'The command has required constructor parameters, but ServiceContainer cannot resolve it automatically.',
                    suggestion: 'Register the command in config/services.php, then list the same class in config/console.php.',
                    docsUrl: 'docs/contributor/module-console-commands.md',
                    details: ['module' => $moduleName, 'file' => $file, 'command' => $entry],
                );
            }

            $command = $reflection->newInstance();
        }

        if (!$command instanceof ConsoleCommandInterface) {
            throw new ZoosperException(
                message: 'Console command must implement ConsoleCommandInterface: ' . $entry,
                context: 'The resolved command is not a valid Zoosper console command.',
                suggestion: 'Implement Zoosper\\Core\\Console\\ConsoleCommandInterface on the command class.',
                docsUrl: 'docs/contributor/module-console-commands.md',
                details: ['module' => $moduleName, 'file' => $file, 'command' => $entry, 'resolved_type' => get_debug_type($command)],
            );
        }

        return $command;
    }
}
