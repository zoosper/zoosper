<?php

declare(strict_types=1);

it('uses the authoritative release version in the API health response', function (): void {
    $root = dirname(__DIR__, 5);
    $version = require $root . '/config/version.php';
    $controller = (string) file_get_contents(
        $root . '/app/zoosper-api/src/Controller/HealthController.php',
    );

    expect($version['version'])->toBe('0.2.0-alpha.1-dev')
        ->and($controller)
        ->toContain("require dirname(__DIR__, 4) . '/config/version.php'")
        ->not->toContain('0.3.0-dev');
});
