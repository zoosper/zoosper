<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\fail;

$repoRootPath = static function (): string {
    $current = __DIR__;
    while ($current !== dirname($current)) {
        if (is_file($current . DIRECTORY_SEPARATOR . 'composer.json') && is_dir($current . DIRECTORY_SEPARATOR . 'app')) {
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

it('documents rate limit middleware integration readiness', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/rate-limit-middleware-integration-readiness.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/rate-limit-middleware-integration-readiness.md'));
    assertStringContainsString('report-only', $contents);
    assertStringContainsString('disabled by default', $contents);
    assertStringContainsString('middleware', $contents);
});

it('provides the rate limit middleware integration audit tool', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-rate-limit-middleware-integration.php'));
    $source = (string) file_get_contents($rootPath('tools/audit-rate-limit-middleware-integration.php'));
    assertStringContainsString('Rate limit middleware integration audit', $source);
    assertStringContainsString('CANDIDATE_INTEGRATION_FILES', $source);
});

it('runs the rate limit middleware integration audit', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-rate-integration-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-rate-limit-middleware-integration.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-middleware-integration.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-middleware-integration.log');

    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-middleware-integration.log');
    assertStringContainsString('RATE_LIMIT_MIDDLEWARE_INTEGRATION_ERRORS 0', $log);
});
