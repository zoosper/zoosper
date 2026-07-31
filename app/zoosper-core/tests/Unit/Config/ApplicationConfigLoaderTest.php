<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Config;

use Zoosper\Core\Config\ApplicationConfigLoader;

function applicationConfigFixture(): string
{
    $basePath = sys_get_temp_dir() . '/zoosper-application-config-' . bin2hex(random_bytes(6));
    $modulePath = $basePath . '/app/example-module';

    mkdir($modulePath . '/config/settings', 0775, true);
    mkdir($basePath . '/config', 0775, true);

    file_put_contents(
        $modulePath . '/module.php',
        "<?php return ['name' => 'example-module', 'enabled' => true];",
    );
    file_put_contents(
        $modulePath . '/config/settings/example.php',
        "<?php return ['source' => 'module', 'nested' => ['preserved' => true, 'winner' => 'module']];",
    );
    file_put_contents(
        $basePath . '/config/example.php',
        "<?php return ['source' => 'root', 'nested' => ['winner' => 'root']];",
    );

    return $basePath;
}

test('application config loader layers module defaults below root overrides', function (): void {
    $config = (new ApplicationConfigLoader(applicationConfigFixture()))->load();

    expect($config->get('example.source'))->toBe('root');
    expect($config->get('example.nested.preserved'))->toBeTrue();
    expect($config->get('example.nested.winner'))->toBe('root');
});
