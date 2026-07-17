<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Console;

use Zoosper\Core\Console\ConsoleApplication;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOutput;

final readonly class RunnableExampleCommand implements ConsoleCommandInterface
{
    public function name(): string
    {
        return 'example:run';
    }

    public function description(): string
    {
        return 'Runs an example command.';
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        $output->writeln('ran ' . implode(',', $args));
        return 7;
    }
}

/**
 * @return array{0: ConsoleOutput, 1: resource, 2: resource}
 */
function bufferedConsoleOutput(): array
{
    $stdout = fopen('php://temp', 'w+');
    $stderr = fopen('php://temp', 'w+');

    if ($stdout === false || $stderr === false) {
        throw new \RuntimeException('Unable to open temporary output buffers.');
    }

    return [new ConsoleOutput($stdout, $stderr), $stdout, $stderr];
}

/** @param resource $stream */
function bufferedConsoleContents(mixed $stream): string
{
    rewind($stream);

    return (string) stream_get_contents($stream);
}

test('help lists built-in and module commands', function () {
    [$output, $stdout] = bufferedConsoleOutput();
    $app = new ConsoleApplication(['migrate'], ['example:run' => new RunnableExampleCommand()]);

    $status = $app->run('help', [], $output);

    expect($status)->toBe(0);
    expect(bufferedConsoleContents($stdout))->toContain('migrate');
    expect(bufferedConsoleContents($stdout))->toContain('example:run');
});

test('runs a module command by name', function () {
    [$output, $stdout] = bufferedConsoleOutput();
    $app = new ConsoleApplication([], ['example:run' => new RunnableExampleCommand()]);

    $status = $app->run('example:run', ['a', 'b'], $output);

    expect($status)->toBe(7);
    expect(bufferedConsoleContents($stdout))->toContain('ran a,b');
});

test('unknown commands return a non-zero status', function () {
    [$output, , $stderr] = bufferedConsoleOutput();
    $app = new ConsoleApplication(['migrate'], []);

    $status = $app->run('missing:command', [], $output);

    expect($status)->toBe(1);
    expect(bufferedConsoleContents($stderr))->toContain('Unknown command: missing:command');
});
