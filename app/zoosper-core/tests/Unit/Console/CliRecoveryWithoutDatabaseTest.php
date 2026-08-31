<?php

declare(strict_types=1);

function runCliWithoutDatabase(string $command): array
{
    $root = dirname(__DIR__, 5);
    $env = array_merge($_ENV, [
        'DB_CONNECTION' => 'mysql',
        'DB_HOST' => '127.0.0.1',
        'DB_PORT' => '1',
        'DB_DATABASE' => 'unreachable',
        'DB_USERNAME' => 'none',
        'DB_PASSWORD' => 'none',
    ]);
    $args = array_merge([PHP_BINARY, $root . '/bin/zoosper'], explode(' ', $command));
    $process = proc_open($args, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root, $env);
    $stdout = '';
    $stderr = '';
    if (is_resource($process)) {
        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
    } else {
        $exitCode = 1;
    }

    return ['exitCode' => $exitCode, 'output' => trim($stdout . ($stderr ? "\n" . $stderr : ''))];
}

it('keeps help and list available when the configured database is unreachable', function (): void {
    foreach (['help', 'list'] as $command) {
        $result = runCliWithoutDatabase($command);
        expect($result['exitCode'])->toBe(0)
            ->and($result['output'])->toContain('Zoosper CLI')
            ->not->toContain('SQLSTATE');
    }
});

it('keeps compile and cache clear available when the configured database is unreachable', function (): void {
    $compile = runCliWithoutDatabase('compile');
    expect($compile['exitCode'])->toBe(0)
        ->and($compile['output'])->toContain('Compiled module manifest:')
        ->not->toContain('SQLSTATE');

    $clear = runCliWithoutDatabase('cache:clear');
    expect($clear['exitCode'])->toBe(0)
        ->and($clear['output'])->toContain('module manifest')
        ->not->toContain('SQLSTATE');
});

it('uses shared layered configuration and keeps PDO lazy until requested', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/bin/zoosper');

    expect($source)->toContain('new ApplicationConfigLoader($basePath, $modules)')
        ->not->toContain('ConfigRepository::fromPath(')
        ->toContain('new PdoConnectionProvider(')
        ->toContain('$services->factory(PDO::class')
        ->toContain('PdoConnectionProvider $connection')
        ->toContain('static fn (): PDO => $connection->get()');
});










