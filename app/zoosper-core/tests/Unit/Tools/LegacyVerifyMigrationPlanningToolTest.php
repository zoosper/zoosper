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

it('provides a read only legacy verify migration planning command', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/plan-legacy-verify-migration.php'));

    $source = (string) file_get_contents($rootPath('tools/plan-legacy-verify-migration.php'));

    assertStringContainsString('--script=', $source);
    assertStringContainsString('This command is read-only', $source);
    assertStringContainsString('Deletion must happen in a focused commit', $source);
});

it('generates a migration plan for one source-owned legacy verify script in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-legacy-verify-plan-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/plan-legacy-verify-migration.php'))
        . ' --script=tools/verify-runtime-path-safety.php'
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);

    $planPath = $outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-migration-plan-verify-runtime-path-safety.txt';

    assertFileExists($planPath);

    $contents = (string) file_get_contents($planPath);

    assertStringContainsString('# Legacy Verify Migration Plan', $contents);
    assertStringContainsString('tools/verify-runtime-path-safety.php', $contents);
    assertStringContainsString('This planner does not delete files', $contents);
});
