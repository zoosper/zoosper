<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Console;

test('schema CLI registers redacted exception handling before PDO creation', function (): void {
    $basePath = dirname(__DIR__, 5);
    $source = (string) file_get_contents($basePath . '/bin/zoosper-schema');

    $register = strpos($source, '$errorHandler->register();');
    $connection = strpos($source, 'new ConnectionFactory(');

    expect($source)->toContain("require \$basePath . '/bootstrap/autoload.php';");
    expect($source)->toContain('new ApplicationConfigLoader(');
    expect($source)->toContain('new LogManager(');
    expect($source)->toContain('$errorHandler->logException($exception);');
    expect($register)->not->toBeFalse();
    expect($connection)->not->toBeFalse();
    expect($register)->toBeLessThan($connection);
});

test('schema CLI does not declare a second env helper or load vendor autoload directly', function (): void {
    $basePath = dirname(__DIR__, 5);
    $source = (string) file_get_contents($basePath . '/bin/zoosper-schema');

    expect($source)->not->toContain('function env(');
    expect($source)->not->toContain("'/vendor/autoload.php'");
    expect($source)->not->toContain('ConfigRepository::fromPath(');
});










