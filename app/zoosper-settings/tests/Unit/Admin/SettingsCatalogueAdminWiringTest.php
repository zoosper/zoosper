<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('owns the protected read-only settings route and menu contribution', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = require $root . '/app/zoosper-settings/config/admin_routes.php';
    $menu = require $root . '/app/zoosper-settings/config/admin_menu.php';

    expect($routes)->toContain([
        'method' => 'GET', 'path' => '/admin/settings',
        'controller' => \Zoosper\Settings\Controller\SettingsCatalogueController::class,
        'action' => 'index', 'permission' => 'settings.manage',
    ])->and($menu[0]['code'])->toBe('settings')
      ->and($menu[0]['permission'])->toBe('settings.manage');
});

it('removes the settings menu contribution from the generic admin shell', function (): void {
    $root = dirname(__DIR__, 5);
    $adminMenu = file_get_contents($root . '/app/zoosper-admin/config/admin_menu.php');
    expect($adminMenu)->not->toContain("'code' => 'settings'");
});

it('renders a searchable module-owned catalogue without persistence controls', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);
    expect($view)->toContain('Search settings, modules and paths')
        ->toContain('data-settings-card')
        ->toContain('Read-only')
        ->toContain('method="get"')
        ->toContain('method="post"')
        ->toContain('action="<?= $e($saveUrl) ?>"')
        ->not->toContain('type="password"');
});










