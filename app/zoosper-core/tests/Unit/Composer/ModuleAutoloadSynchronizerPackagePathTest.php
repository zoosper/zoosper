<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

use Zoosper\Core\Composer\ModuleAutoloadSynchronizer;

test('autoload synchronizer discovers package path modules after app compatibility path is removed', function () {
    $root = sys_get_temp_dir() . '/zoosper-package-autoload-' . bin2hex(random_bytes(4));
    mkdir($root . '/packages/zoosper-media/src/Service', 0775, true);
    mkdir($root . '/packages/zoosper-media/tests/Unit', 0775, true);
    file_put_contents($root . '/packages/zoosper-media/module.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn ['name' => 'Zoosper_Media', 'enabled' => true];\n");

    $mappings = (new ModuleAutoloadSynchronizer($root))->discoverMappings();

    expect($mappings['autoload'])->toHaveKey('Zoosper\\Media\\');
    expect($mappings['autoload']['Zoosper\\Media\\'])->toBe('packages/zoosper-media/src/');
    expect($mappings['autoload-dev']['Zoosper\\Media\\Tests\\'])->toBe('packages/zoosper-media/tests/');
});
