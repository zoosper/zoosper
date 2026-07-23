<?php

declare(strict_types=1);

use Zoosper\Core\Config\LayeredConfigLoader;

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

it('merges layered config with later layers taking precedence', function (): void {
    $result = (new LayeredConfigLoader())->load([
        ['source' => 'core', 'config' => ['cache' => ['enabled' => false, 'ttl' => 60]]],
        ['source' => 'root', 'config' => ['cache' => ['enabled' => true]]],
    ]);

    assertSame(['core', 'root'], $result->sources);
    assertSame(true, $result->config['cache']['enabled']);
    assertSame(60, $result->config['cache']['ttl']);
});

it('replaces list arrays instead of appending them', function (): void {
    $result = (new LayeredConfigLoader())->load([
        ['source' => 'module', 'config' => ['middleware' => ['auth', 'csrf']]],
        ['source' => 'root', 'config' => ['middleware' => ['custom']]],
    ]);

    assertSame(['custom'], $result->config['middleware']);
});

it('documents config layering foundation', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/config-layering-foundation.md'));
    assertFileExists($rootPath('docs/architecture/adr-config-layering.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/config-layering-foundation.md'));
    assertStringContainsString('core package defaults', $contents);
    assertStringContainsString('runtime/in-memory overrides', $contents);
});

it('runs the config layering foundation audit', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-config-layering-foundation.php'));
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-config-layering-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-config-layering-foundation.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'config-layering-foundation.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'config-layering-foundation.log');
    assertStringContainsString('CONFIG_LAYERING_FOUNDATION_ERRORS 0', $log);
});
