<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigFileLayeredLoader;
use Zoosper\Core\Config\ConfigLayerSource;

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

it('loads layered config from named PHP config files', function (): void {
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-config-file-layer-' . bin2hex(random_bytes(6));
    mkdir($dir, 0775, true);
    file_put_contents($dir . '/base.php', '<?php return ["ui" => ["enabled" => false, "title" => "Base"]];');
    file_put_contents($dir . '/override.php', '<?php return ["ui" => ["enabled" => true]];');

    $result = (new ConfigFileLayeredLoader())->load([
        new ConfigLayerSource('base', $dir . '/base.php'),
        new ConfigLayerSource('override', $dir . '/override.php'),
    ]);

    assertSame(['base', 'override'], $result->sources);
    assertSame(true, $result->config['ui']['enabled']);
    assertSame('Base', $result->config['ui']['title']);
});

it('documents the admin UI form config layering pilot', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/config-layering-admin-ui-form-pilot.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/config-layering-admin-ui-form-pilot.md'));
    assertStringContainsString('admin_forms.php', $contents);
    assertStringContainsString('admin_ui.php', $contents);
});

it('provides pilot audit and smoke tools', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/smoke-admin-ui-form-config-layering.php'));
    assertFileExists($rootPath('tools/audit-config-layering-admin-ui-form-pilot.php'));
});
