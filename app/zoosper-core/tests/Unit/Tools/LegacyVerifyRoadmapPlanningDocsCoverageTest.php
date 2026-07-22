<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertDirectoryExists;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\fail;

$repoRootPath = static function (): string {
    $current = __DIR__;

    while ($current !== dirname($current)) {
        if (is_file($current . DIRECTORY_SEPARATOR . 'composer.json') && is_dir($current . DIRECTORY_SEPARATOR . 'tools')) {
            return $current;
        }

        $current = dirname($current);
    }

    fail('Unable to locate Zoosper repository root from ' . __DIR__);
};

$rootPath = static function (string $path = '') use ($repoRootPath): string {
    $root = $repoRootPath();

    return $path === '' ? $root : $root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
};

it('keeps roadmap planning docs legacy script source owned before ledger promotion', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/verify-roadmap-planning-docs.php'));
    assertFileExists($rootPath('docs/development/legacy-verify-migration-status.md'));

    $status = (string) file_get_contents($rootPath('docs/development/legacy-verify-migration-status.md'));

    assertStringContainsString('| `tools/verify-roadmap-planning-docs.php` | source-owned |', $status);
});

it('keeps development documentation directory and migration policy docs discoverable', function () use ($rootPath): void {
    assertDirectoryExists($rootPath('docs/development'));
    assertFileExists($rootPath('docs/development/legacy-verify-pest-migration-pilot.md'));
    assertFileExists($rootPath('docs/development/legacy-verify-migration-status.md'));
    assertFileExists($rootPath('docs/development/legacy-verify-migration-coverage-map.md'));
    assertFileExists($rootPath('docs/development/legacy-verify-controlled-removal.md'));
});

it('keeps roadmap planning candidate visible in migration docs', function () use ($rootPath): void {
    foreach ([
        'docs/development/legacy-verify-migration-status.md',
        'docs/development/legacy-verify-migration-coverage-map.md',
    ] as $doc) {
        $contents = (string) file_get_contents($rootPath($doc));
        assertStringContainsString('tools/verify-roadmap-planning-docs.php', $contents);
    }
});

it('keeps controlled removal protections available for roadmap planning docs candidate', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/remove-migrated-legacy-verify.php'));

    $source = (string) file_get_contents($rootPath('tools/remove-migrated-legacy-verify.php'));

    assertStringContainsString('tools/verify-roadmap-planning-docs.php', $source);
    assertStringContainsString('migrationStatusFor', $source);
    assertStringContainsString('allowedPilotScripts', $source);
});

it('provides read only evidence tooling for the roadmap planning docs migration candidate', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-verify-roadmap-planning-docs-migration.php'));
    assertFileExists($rootPath('docs/development/verify-roadmap-planning-docs-migration.md'));

    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-roadmap-docs-migration-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-verify-roadmap-planning-docs-migration.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'verify-roadmap-planning-docs-migration.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'verify-roadmap-planning-docs-migration.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'verify-roadmap-planning-docs-migration.txt');

    assertStringContainsString('Legacy script: tools/verify-roadmap-planning-docs.php', $report);
    assertStringContainsString('Migration status: source-owned', $report);
    assertStringContainsString('Errors: 0', $report);
});

it('still refuses deletion while roadmap planning docs remains source owned', function () use ($rootPath): void {
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/remove-migrated-legacy-verify.php'))
        . ' --script=tools/verify-roadmap-planning-docs.php --apply --confirm-pest-coverage --confirm-remove';

    exec($command, $output, $exitCode);

    assertTrue($exitCode !== 0);
    assertFileExists($rootPath('tools/verify-roadmap-planning-docs.php'));
});
