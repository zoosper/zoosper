<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
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

it('provides a read only pilot batch readiness audit command', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-legacy-verify-pilot-batch-readiness.php'));
    assertFileExists($rootPath('docs/development/legacy-verify-migration-ledger.md'));

    $source = (string) file_get_contents($rootPath('tools/audit-legacy-verify-pilot-batch-readiness.php'));

    assertStringContainsString('legacy-verify-pilot-batch-readiness.txt', $source);
    assertStringContainsString('This command is read-only', $source);
    assertStringContainsString('equivalent Pest coverage required before deletion', $source);
});

it('generates pilot batch readiness reports in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-pilot-readiness-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-legacy-verify-pilot-batch-readiness.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-pilot-batch-readiness.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-pilot-batch-readiness.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-pilot-batch-readiness.txt');

    assertStringContainsString('# Legacy Verify Pilot Batch Readiness', $report);
    assertStringContainsString('tools/verify-project-structure.php', $report);
    assertStringContainsString('tools/verify-runtime-path-safety.php', $report);
    assertStringContainsString('Migration gate: equivalent Pest coverage required before deletion', $report);
});
