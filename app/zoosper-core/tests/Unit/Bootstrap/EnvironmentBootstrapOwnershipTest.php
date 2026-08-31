<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Bootstrap;

test('application bootstrap is the sole environment-loader implementation', function (): void {
    $basePath = dirname(__DIR__, 5);

    expect($basePath . '/app/zoosper-core/src/Bootstrap/EnvLoader.php')->not->toBeFile();
    expect($basePath . '/app/zoosper-core/src/Env/EnvFileLoader.php')->not->toBeFile();

    $bootstrap = (string) file_get_contents($basePath . '/bootstrap/autoload.php');
    expect($bootstrap)->toContain("if (!function_exists('env'))");
    expect($bootstrap)->toContain('function zoosperParseEnvValue(string $rawValue): string');
});

test('repository tools delegate to the application bootstrap', function (): void {
    $basePath = dirname(__DIR__, 5);
    $toolsBootstrap = (string) file_get_contents($basePath . '/tools/bootstrap.php');

    expect($toolsBootstrap)->toContain("require_once \$basePath . '/bootstrap/autoload.php';");
    expect($toolsBootstrap)->not->toContain('EnvFileLoader');
    expect($toolsBootstrap)->not->toContain('function env(');
    expect($toolsBootstrap)->not->toContain("'/vendor/autoload.php'");
});

test('shared tool bootstrap returns the repository root', function (): void {
    $basePath = dirname(__DIR__, 5);
    $returnedPath = require $basePath . '/tools/bootstrap.php';

    expect($returnedPath)->toBe($basePath);
    expect(function_exists('env'))->toBeTrue();
});










