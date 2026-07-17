<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

use Zoosper\Core\Composer\ModuleAutoloadSynchronizer;

function autoloadSyncFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-autoload-sync-' . bin2hex(random_bytes(4));
    mkdir($root . '/app/zoosper-media/src/Service', 0775, true);
    mkdir($root . '/app/zoosper-media/tests/Unit', 0775, true);
    file_put_contents($root . '/app/zoosper-media/module.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn ['name' => 'Zoosper_Media', 'enabled' => true];\n");
    file_put_contents($root . '/composer.json', json_encode(['autoload' => ['psr-4' => []], 'autoload-dev' => ['psr-4' => []]], JSON_PRETTY_PRINT));

    return $root;
}

test('discovers module source and test psr-4 mappings from module metadata', function () {
    $root = autoloadSyncFixture();
    $mappings = (new ModuleAutoloadSynchronizer($root))->discoverMappings();

    expect($mappings['autoload'])->toHaveKey('Zoosper\\Media\\');
    expect($mappings['autoload']['Zoosper\\Media\\'])->toBe('app/zoosper-media/src/');
    expect($mappings['autoload-dev'])->toHaveKey('Zoosper\\Media\\Tests\\');
    expect($mappings['autoload-dev']['Zoosper\\Media\\Tests\\'])->toBe('app/zoosper-media/tests/');
});

test('sync updates composer json without replacing existing mappings', function () {
    $root = autoloadSyncFixture();
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true);
    $composer['autoload']['psr-4']['Existing\\'] = 'existing/src/';
    file_put_contents($root . '/composer.json', json_encode($composer, JSON_PRETTY_PRINT));

    $result = (new ModuleAutoloadSynchronizer($root))->sync();
    $updated = json_decode((string) file_get_contents($root . '/composer.json'), true);

    expect($result['changed'])->toBeTrue();
    expect($updated['autoload']['psr-4']['Existing\\'])->toBe('existing/src/');
    expect($updated['autoload']['psr-4']['Zoosper\\Media\\'])->toBe('app/zoosper-media/src/');
});
