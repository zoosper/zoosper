<?php

declare(strict_types=1);

it('keeps Dashboard contracts dependency-safe and concrete feature code out of Admin composition', function (): void {
    $root = dirname(__DIR__, 5);
    $package = json_decode((string) file_get_contents($root . '/packages/zoosper-admin-dashboard/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $admin = json_decode((string) file_get_contents($root . '/app/zoosper-admin/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $auth = json_decode((string) file_get_contents($root . '/app/zoosper-auth/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $controller = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/DashboardController.php');
    $dashboardSources = '';
    foreach (glob($root . '/app/zoosper-admin/src/Dashboard/*.php') ?: [] as $source) {
        $dashboardSources .= (string) file_get_contents($source);
    }
    $contributor = (string) file_get_contents($root . '/app/zoosper-auth/src/Dashboard/AdminUserCountDashboardWidgetContributor.php');

    expect(array_keys($package['require']))->toBe(['php'])
        ->and($admin['require']['zoosper/admin-dashboard'])->toBe('dev-dev')
        ->and($auth['require']['zoosper/admin-dashboard'])->toBe('dev-dev')
        ->and($controller)->not->toContain('ServiceContainer')
        ->not->toContain('Zoosper\Auth\Repository')
        ->and($dashboardSources)->not->toContain('Zoosper\AdminGrid')
        ->and($contributor)->not->toContain('Zoosper\Admin\\');
});










