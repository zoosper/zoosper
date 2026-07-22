<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\fail;

$repoRootPath = static function (): string {
    $current = __DIR__;
    while ($current !== dirname($current)) {
        if (is_file($current . DIRECTORY_SEPARATOR . 'composer.json') && is_dir($current . DIRECTORY_SEPARATOR . 'tools')) return $current;
        $current = dirname($current);
    }
    fail('Unable to locate Zoosper repository root from ' . __DIR__);
};
$rootPath = static fn (string $path = ''): string => ($r = $repoRootPath()) && $path === '' ? $r : $r . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);

it('records migrated pilot scripts as removed', function () use ($rootPath): void {
    foreach (['tools/verify-project-structure.php', 'tools/verify-runtime-path-safety.php', 'tools/verify-service-provider-manifest-file.php'] as $script) {
        assertFileDoesNotExist($rootPath($script));
    }
    $status = (string) file_get_contents($rootPath('docs/development/legacy-verify-migration-status.md'));
    assertStringContainsString('| `tools/verify-service-provider-manifest-file.php` | migrated |', $status);
});

it('refuses apply for remaining source owned scripts even with confirmation flags', function () use ($rootPath): void {
    $script = $rootPath('tools/verify-module-composer-manifests.php');
    assertFileExists($script);
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($rootPath('tools/remove-migrated-legacy-verify.php')) . ' --script=tools/verify-module-composer-manifests.php --apply --confirm-pest-coverage --confirm-remove';
    exec($command, $output, $exitCode);
    assertTrue($exitCode !== 0);
    assertFileExists($script);
});

it('keeps dry run available for source owned scripts', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-ledger-aware-removal-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($rootPath('tools/remove-migrated-legacy-verify.php')) . ' --script=tools/verify-module-composer-manifests.php --output-dir=' . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-controlled-removal-verify-module-composer-manifests.txt');
});
