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

it('records service provider manifest legacy script as migrated after retirement', function () use ($rootPath): void {
    assertFileDoesNotExist($rootPath('tools/verify-service-provider-manifest-file.php'));
    $status = (string) file_get_contents($rootPath('docs/development/legacy-verify-migration-status.md'));
    assertStringContainsString('| `tools/verify-service-provider-manifest-file.php` | migrated |', $status);
});

it('keeps service provider manifest related source signals discoverable', function () use ($rootPath): void {
    $haystack = '';
    foreach (['app', 'config', 'tools'] as $path) {
        if (! is_dir($rootPath($path))) continue;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath($path), FilesystemIterator::SKIP_DOTS)) as $file) {
            if (!$file->isFile() || !in_array($file->getExtension(), ['php', 'md', 'json'], true)) continue;
            $haystack .= (string) file_get_contents($file->getPathname());
            if (str_contains($haystack, 'ServiceProvider') || str_contains($haystack, 'service provider')) break 2;
        }
    }
    assertTrue(str_contains($haystack, 'ServiceProvider') || str_contains($haystack, 'service provider'));
});

it('provides read only evidence tooling for the service provider manifest migration candidate', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-verify-service-provider-manifest-migration.php'));
    assertFileExists($rootPath('docs/development/verify-service-provider-manifest-migration.md'));
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-sp-manifest-migration-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($rootPath('tools/audit-verify-service-provider-manifest-migration.php')) . ' --output-dir=' . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'verify-service-provider-manifest-migration.txt');
    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'verify-service-provider-manifest-migration.txt');
    assertStringContainsString('Legacy script: tools/verify-service-provider-manifest-file.php', $report);
    assertStringContainsString('Migration status: migrated', $report);
    assertStringContainsString('Errors: 0', $report);
});
