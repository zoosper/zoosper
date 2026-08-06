<?php

declare(strict_types=1);

it('keeps console command discovery on the canonical environment bootstrap', function (): void {
    $root = dirname(__DIR__, 5);
    $source = file_get_contents(
        $root . '/app/zoosper-core/tests/Unit/Console/ConsoleCommandModuleDiscoveryTest.php',
    );

    expect($source)
        ->toContain("require_once dirname(__DIR__, 5) . '/bootstrap/autoload.php';")
        ->not->toContain("if (!function_exists('env'))")
        ->not->toContain('function env(string $key');
});
