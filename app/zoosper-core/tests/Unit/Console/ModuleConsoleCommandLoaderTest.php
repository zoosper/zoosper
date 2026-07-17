<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Console;

use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Console\ModuleConsoleCommandLoader;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Exception\ZoosperException;
use Zoosper\Core\Module\ModuleRegistry;

final readonly class ExampleDiscoveredCommand implements ConsoleCommandInterface
{
    public function name(): string
    {
        return 'example:discovered';
    }

    public function description(): string
    {
        return 'Example discovered command.';
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        $output->writeln('ok');
        return 0;
    }
}

final readonly class ExampleServiceCommand implements ConsoleCommandInterface
{
    public function __construct(private string $label)
    {
    }

    public function name(): string
    {
        return 'example:service';
    }

    public function description(): string
    {
        return $this->label;
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        $output->writeln($this->label);
        return 0;
    }
}

function consoleCommandTempRoot(): string
{
    $root = sys_get_temp_dir() . '/zoosper-console-' . bin2hex(random_bytes(6));
    mkdir($root . '/app/acme-console/config', 0775, true);
    file_put_contents($root . '/app/acme-console/module.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn ['name' => 'Acme_Console', 'enabled' => true];\n");

    return $root;
}

test('loads module console commands from config file', function () {
    $root = consoleCommandTempRoot();
    file_put_contents($root . '/app/acme-console/config/console.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn [\\Zoosper\\Core\\Tests\\Unit\\Console\\ExampleDiscoveredCommand::class];\n");

    $commands = (new ModuleConsoleCommandLoader(new ModuleRegistry($root), new ServiceContainer()))->load();

    expect($commands)->toHaveKey('example:discovered');
});

test('resolves dependency-backed console commands from the service container', function () {
    $root = consoleCommandTempRoot();
    file_put_contents($root . '/app/acme-console/config/console.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn [\\Zoosper\\Core\\Tests\\Unit\\Console\\ExampleServiceCommand::class];\n");

    $services = new ServiceContainer();
    $services->set(ExampleServiceCommand::class, new ExampleServiceCommand('From container'));

    $commands = (new ModuleConsoleCommandLoader(new ModuleRegistry($root), $services))->load();

    expect($commands)->toHaveKey('example:service');
    expect($commands['example:service']->description())->toBe('From container');
});

test('throws a helpful error when console config is not an array', function () {
    $root = consoleCommandTempRoot();
    file_put_contents($root . '/app/acme-console/config/console.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn 'bad';\n");

    (new ModuleConsoleCommandLoader(new ModuleRegistry($root), new ServiceContainer()))->load();
})->throws(ZoosperException::class, 'Console command config must return an array');
