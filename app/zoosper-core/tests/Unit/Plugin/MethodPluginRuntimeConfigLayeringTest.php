<?php

declare(strict_types=1);

use Zoosper\Core\Plugin\MethodPluginRuntimeConfigLayeredLoader;
use Zoosper\Core\Plugin\MethodPluginRuntimeConfigLoader;

it('loads disabled method plugin runtime config from array config', function (): void {
    $config = (new MethodPluginRuntimeConfigLoader())->load([
        'method_plugins' => [
            'enabled' => false,
            'report_only' => true,
            'allow_list' => ['Zoosper\\Page\\Service\\PageRenderer::render'],
        ],
    ]);

    expect($config->enabled)->toBeFalse();
    expect($config->reportOnly)->toBeTrue();
    expect($config->reportOnlyInvocationKeys)->toBe([]);
});

it('loads layered method plugin runtime config with root override disabled', function (): void {
    $dir = sys_get_temp_dir() . '/zoosper-method-plugin-runtime-layering-test-' . bin2hex(random_bytes(6));
    mkdir($dir, 0775, true);

    $moduleFile = $dir . '/module.php';
    $rootFile = $dir . '/root.php';

    file_put_contents($moduleFile, "<?php\nreturn ['method_plugins' => ['enabled' => true, 'report_only' => true, 'allow_list' => ['Zoosper\\\\Page\\\\Service\\\\PageRenderer::render']]];\n");
    file_put_contents($rootFile, "<?php\nreturn ['method_plugins' => ['enabled' => false, 'report_only' => true, 'allow_list' => []]];\n");

    $config = (new MethodPluginRuntimeConfigLayeredLoader())->load([
        'module:test' => $moduleFile,
        'root:test' => $rootFile,
    ]);

    expect($config->enabled)->toBeFalse();
    expect($config->reportOnly)->toBeTrue();
    expect($config->reportOnlyInvocationKeys)->toBe([]);
});

it('keeps method plugin runtime config layering tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-method-plugin-runtime-config-layering.php')->toBeFile();
    expect($root . '/tools/audit-method-plugin-runtime-config-layering.php')->toBeFile();
});
