<?php

declare(strict_types=1);

it('owns one alpha version and exposes release commands', function (): void {
    $root = dirname(__DIR__, 5);
    $version = require $root . '/config/version.php';
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $bin = (string) file_get_contents($root . '/bin/zoosper');
    expect($version['version'])->toBe('0.1.0-alpha.1')
        ->and($composer)->not->toHaveKey('version')
        ->and($composer['scripts'])->toHaveKey('release:check')
        ->and($bin)->toContain("'release:check' => static fn ()")
        ->toContain("'version' => static fn ()");
});

it('ships the alpha operator documentation set', function (): void {
    $root = dirname(__DIR__, 5);
    foreach (['CHANGELOG.md','docs/installation.md','docs/upgrade.md','docs/deployment.md','docs/troubleshooting.md','docs/alpha-release-checklist.md'] as $file) {
        expect($root . '/' . $file)->toBeFile()->and(filesize($root . '/' . $file))->toBeGreaterThan(100);
    }
});
