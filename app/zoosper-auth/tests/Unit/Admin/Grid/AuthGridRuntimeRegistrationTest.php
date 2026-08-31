<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AdminUserGridPageBuilder;
use Zoosper\Auth\Admin\Grid\AuthGridPageBuilderFactory;
use Zoosper\Auth\Admin\Grid\RoleGridPageBuilder;

it('loads the Auth Grid read-side services through the active Auth manifest', function (): void {
    $root = dirname(__DIR__, 6);
    $services = require $root . '/app/zoosper-auth/config/services.php';

    expect($services)->toBeArray()
        ->and($services)->toHaveKeys([
            AuthGridPageBuilderFactory::class,
            AdminUserGridPageBuilder::class,
            RoleGridPageBuilder::class,
        ]);
});

it('registers the fragment exactly once with existing services retaining precedence', function (): void {
    $root = dirname(__DIR__, 6);
    $source = (string) file_get_contents(
        $root . '/app/zoosper-auth/config/services.php',
    );
    $marker = "...require __DIR__ . '/services_auth_grid.php',";

    expect(substr_count($source, $marker))->toBe(1)
        ->and(strpos($source, $marker))->toBeLessThan(
            strpos($source, '=>', strpos($source, 'return ['))
        );
});










