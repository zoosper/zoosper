<?php

declare(strict_types=1);

$repoRootPath = static function (): string {
    $current = __DIR__;
    while ($current !== dirname($current)) {
        if (is_file($current . DIRECTORY_SEPARATOR . 'composer.json') && is_dir($current . DIRECTORY_SEPARATOR . 'tools')) {
            return $current;
        }
        $current = dirname($current);
    }
    \PHPUnit\Framework\fail('Unable to locate Zoosper repository root from ' . __DIR__);
};

$rootPath = static function (string $path = '') use ($repoRootPath): string {
    $root = $repoRootPath();
    return $path === '' ? $root : $root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
};

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringNotContainsString;

it('generates tools inventory reports without first pilot scripts', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/generate-tools-inventory-report.php'));
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-tools-inventory-' . bin2hex(random_bytes(6));
    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($rootPath('tools/generate-tools-inventory-report.php')) . ' --output-dir=' . escapeshellarg($outputDir), $output, $exitCode);
    assertSame(0, $exitCode);
    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'tools-inventory.txt');
    foreach ([
        'tools/verify-project-structure.php',
        'tools/verify-runtime-path-safety.php',
        'tools/verify-service-provider-manifest-file.php',
        'tools/verify-module-composer-manifests.php',
        'tools/verify-roadmap-planning-docs.php',
    ] as $script) {
        assertStringNotContainsString($script, $report);
    }
});
