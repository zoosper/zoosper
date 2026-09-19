<?php

declare(strict_types=1);

it('defines a deterministic allow-listed production artifact with isolated verification', function (): void {
    $root = dirname(__DIR__, 5);
    $tool = (string) file_get_contents($root . '/tools/build-production-artifact.php');
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($composer['scripts']['artifact:build'] ?? null)->toBe('@php tools/build-production-artifact.php')
        ->and($tool)->toContain('const ROOT_FILES')
        ->toContain('const ROOT_DIRECTORIES')
        ->toContain('const PROHIBITED_SEGMENTS')
        ->toContain("'--no-dev'")
        ->toContain("'--classmap-authoritative'")
        ->toContain('materialiseVendorSymlinks')
        ->toContain("removeTree(\$source . '/app')")
        ->toContain("removeTree(\$source . '/packages')")
        ->toContain("'vendor/zoosper/core/module.php'")
        ->toContain("'vendor/zoosper/database/module.php'")
        ->toContain("'--sort=name'")
        ->toContain("'--mtime=@' . \$commitTime")
        ->toContain("'/RELEASE-MANIFEST.json'")
        ->toContain("preg_match('/\\b(\\d+\\.\\d+\\.\\d+")
        ->toContain("hash_file('sha256'")
        ->toContain("'module:manifest:check'")
        ->toContain('assertProductionTree($verify)')
        ->toContain('pruneProductionTree');
});

it('excludes development and sensitive repository families from the artifact policy', function (): void {
    $root = dirname(__DIR__, 5);
    $tool = (string) file_get_contents($root . '/tools/build-production-artifact.php');
    foreach (['.github', '.githooks', '.claude', 'tests', 'fixtures', 'docs-site', 'tools', 'psalm-baseline.xml', 'package-lock.json'] as $prohibited) {
        expect($tool)->toContain("'{$prohibited}'");
    }
});
