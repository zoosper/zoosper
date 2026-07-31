<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use Composer\InstalledVersions;
use Zoosper\Core\Module\ModuleRegistry;

test('installed Composer version is used for a first-party runtime module', function (): void {
    $basePath = dirname(__DIR__, 5);
    $modules = (new ModuleRegistry($basePath, $basePath . '/var/cache/non-existent-test-modules.php'))
        ->discoverModulesLive();

    $core = null;
    foreach ($modules as $module) {
        if ($module->name === 'zoosper-core') {
            $core = $module;
            break;
        }
    }

    expect($core)->not->toBeNull();
    expect($core->version)->toBe(
        InstalledVersions::getPrettyVersion('zoosper/core')
            ?? InstalledVersions::getVersion('zoosper/core'),
    );
});
