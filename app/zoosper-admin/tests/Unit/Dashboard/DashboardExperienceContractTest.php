<?php

declare(strict_types=1);

use Zoosper\Admin\Dashboard\DashboardQuickLinks;
use Zoosper\Admin\Navigation\AdminMenuItem;

it('builds Dashboard shortcuts from discovered permission-filtered menu items', function (): void {
    $links = (new DashboardQuickLinks())->fromMenuItems([
        new AdminMenuItem('dashboard', 'Dashboard', '/admin'),
        new AdminMenuItem('pages', 'Pages', '/admin/pages', icon: 'pages'),
        new AdminMenuItem('placeholder', 'Placeholder', '#'),
        new AdminMenuItem('duplicate-pages', 'Duplicate', '/admin/pages'),
        new AdminMenuItem('themes', 'Themes', '/admin/themes', icon: 'themes'),
    ]);

    expect($links)->toBe([
        ['code' => 'pages', 'label' => 'Pages', 'url' => '/admin/pages', 'icon' => 'pages'],
        ['code' => 'themes', 'label' => 'Themes', 'url' => '/admin/themes', 'icon' => 'themes'],
    ]);
});

it('keeps the Dashboard server rendered escaped and free from inline behaviour', function (): void {
    $root = dirname(__DIR__, 5);
    $moduleView = (string) file_get_contents($root . '/app/zoosper-admin/resources/views/dashboard/index.php');
    $themeView = (string) file_get_contents($root . '/themes/admin/default/templates/modules/zoosper-admin/dashboard/index.php');
    $css = (string) file_get_contents($root . '/app/zoosper-admin/resources/assets/css/admin-components.css');

    expect($moduleView)->toBe($themeView)
        ->toContain('class="dashboard-links"')
        ->toContain('aria-label="Available Admin workspaces"')
        ->toContain('<?= $e($link[\'url\']) ?>')
        ->toContain('<?= $e($link[\'label\']) ?>')
        ->not->toMatch('/\\son[a-z]+\\s*=/i')
        ->not->toMatch('/\\sstyle\\s*=/i')
        ->not->toContain('<script')
        ->and($css)->toContain('.dashboard-overview')
        ->toContain('.dashboard-link')
        ->toContain(':root[data-admin-theme="dark"] .dashboard-link:hover')
        ->toContain('@media (max-width: 680px)')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

it('keeps Dashboard discovery permission filtered at the Admin menu boundary', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/DashboardController.php');

    expect($controller)->toContain('$this->menu->itemsFor($user)')
        ->toContain("template: 'zoosper-admin::dashboard/index'")
        ->not->toContain('new AdminMenuItem')
        ->not->toContain('/admin/pages')
        ->not->toContain('/admin/themes');
});
