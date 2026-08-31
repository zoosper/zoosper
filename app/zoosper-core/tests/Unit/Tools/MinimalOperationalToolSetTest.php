<?php

declare(strict_types=1);

it('keeps only active root operational and verification tools', function (): void {
    $root = dirname(__DIR__, 5);
    $expected = [
        'README.md',
        'audit-module-package-readiness.php',
        'bootstrap.php',
        'cleanup-expired-rate-limit-buckets.php',
        'gate.php',
        'install-git-hooks.php',
        'site-lookup.php',
        'verify-latte-template-engine.php',
        'verify-module-dependencies.php',
        'verify-service-providers.php',
    ];
    $actual = array_map('basename', glob($root . '/tools/*') ?: []);
    sort($actual);
    sort($expected);

    expect($actual)->toBe($expected);
});

it('does not ship completed package migration tools or tracked uploads', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/packages/zoosper-media/tools')->not->toBeDirectory()
        ->and($root . '/public/media/.gitkeep')->toBeFile();
});










