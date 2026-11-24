<?php

declare(strict_types=1);

use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\Navigation\AdminMenuLoader;
use Zoosper\Admin\Navigation\AdminSectionBuilder;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Module\ModuleRegistry;

it('renders the release version by default and respects configured overrides', function (): void {
    $root = dirname(__DIR__, 5);
    $release = require $root . '/config/version.php';
    $menu = new AdminMenu(new AdminMenuLoader(new ModuleRegistry($root . '/app')), new AdminSectionBuilder());

    $default = new AdminLayout(menu: $menu);
    $configured = new AdminLayout(menu: $menu, config: ConfigRepository::fromArray([
        'app' => ['version' => '<custom>'],
    ]));

    expect($default->render('Dashboard', '', null))->toContain('Zoosper CMS ' . $release['version'])
        ->and($configured->render('Dashboard', '', null))->toContain('Zoosper CMS &lt;custom&gt;');
});