<?php

declare(strict_types=1);

use Zoosper\Core\Module\Module;
use Zoosper\Core\Module\ModuleManifestCompiler;

it('compiles configuration only for explicit boolean discovery flags', function (): void {
    $root = sys_get_temp_dir() . '/zoosper-compiler-flags-' . bin2hex(random_bytes(6));
    $modulePath = $root . '/app/acme-flags';
    mkdir($modulePath . '/config', 0775, true);
    mkdir($root . '/var/cache', 0775, true);

    file_put_contents($modulePath . '/config/services.php', "<?php return ['service' => true];\n");
    file_put_contents($modulePath . '/config/service_decorators.php', "<?php return ['decorator' => true];\n");
    file_put_contents($modulePath . '/config/admin_routes.php', "<?php return ['admin' => true];\n");
    file_put_contents($modulePath . '/config/api_routes.php', "<?php return ['api' => true];\n");

    $module = new Module(
        name: 'acme-flags',
        path: $modulePath,
        discovery: [
            'services' => 1,
            'service_decorators' => '1',
            'routes_admin' => true,
            'routes_api' => 1,
        ],
    );
    $compiler = new ModuleManifestCompiler($root);

    $compileServices = new \ReflectionMethod($compiler, 'compileServices');
    $compileServices->invoke($compiler, [$module]);

    $compileRoutes = new \ReflectionMethod($compiler, 'compileRoutes');
    $compileRoutes->invoke($compiler, [$module], 'admin_routes.php', 'routes_admin_compiled.php');
    $compileRoutes->invoke($compiler, [$module], 'api_routes.php', 'routes_api_compiled.php');

    $services = require $root . '/var/cache/services_compiled.php';
    $adminRoutes = require $root . '/var/cache/routes_admin_compiled.php';
    $apiRoutes = require $root . '/var/cache/routes_api_compiled.php';

    expect($services)->toBe([])
        ->and($adminRoutes)->toBe(['admin' => true])
        ->and($apiRoutes)->toBe([]);

    exec('rm -rf ' . escapeshellarg($root));
});
