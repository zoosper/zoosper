<?php

declare(strict_types=1);

use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\Contracts\MenuItemInterface;
use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\Navigation\AdminMenuItem;
use Zoosper\Admin\Navigation\AdminMenuLoader;
use Zoosper\Admin\Navigation\AdminNavigationRenderer;
use Zoosper\Admin\Navigation\AdminSectionBuilder;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Core\Module\ModuleRegistry;

it('discovers and structures nested menu items into multi-tier sections', function (): void {
    $items = [
        new AdminMenuItem('dashboard', 'Dashboard', '/admin', 'admin.access', sortOrder: 10, group: 'Content', icon: 'dashboard'),
        new AdminMenuItem('pages', 'Pages', '/admin/pages', 'page.manage', sortOrder: 20, group: 'Content', icon: 'pages'),
        new AdminMenuItem('pages-create', 'New Page', '/admin/pages/create', 'page.manage', parent: 'pages', sortOrder: 10, group: 'Content'),
        new AdminMenuItem('pages-categories', 'Categories', '/admin/pages/categories', 'page.manage', parent: 'pages', sortOrder: 20, group: 'Content'),
        new AdminMenuItem('settings', 'Settings', '/admin/settings', 'settings.manage', sortOrder: 90, group: 'System', icon: 'settings'),
    ];

    $builder = new AdminSectionBuilder();
    $sections = $builder->build($items);

    expect($sections)->toHaveCount(2)
        ->and($sections[0]->getId())->toBe('content')
        ->and($sections[0]->getMenuItems())->toHaveCount(2);

    $pagesItem = $sections[0]->getMenuItems()[1];
    expect($pagesItem)->toBeInstanceOf(AdminMenuItem::class)
        ->and($pagesItem->getId())->toBe('pages')
        ->and($pagesItem->hasChildren())->toBeTrue()
        ->and($pagesItem->getChildren())->toHaveCount(2)
        ->and($pagesItem->getChildren()[0]->getId())->toBe('pages-create')
        ->and($pagesItem->getChildren()[1]->getId())->toBe('pages-categories');
});

it('filters permissions for multi-layer hierarchy before rendering', function (): void {
    $baseDir = sys_get_temp_dir() . '/zoosper_hierarchy_' . bin2hex(random_bytes(6));
    $moduleDir = $baseDir . '/app/zoosper-test-users';
    mkdir($moduleDir . '/config', 0777, true);
    file_put_contents(
        $moduleDir . '/module.php',
        '<?php return ["name" => "zoosper-test-users", "enabled" => true, "sort_order" => 10];',
    );
    file_put_contents(
        $moduleDir . '/config/admin_menu.php',
        '<?php return [
            ["code" => "users", "label" => "Users", "url" => "/admin/users", "permission" => "user.manage", "group" => "Users", "sort_order" => 10, "icon" => "users"],
            ["code" => "users-create", "label" => "Add User", "url" => "/admin/users/create", "permission" => "user.create", "parent" => "users", "group" => "Users", "sort_order" => 10],
            ["code" => "users-export", "label" => "Export Users", "url" => "/admin/users/export", "permission" => "user.export", "parent" => "users", "group" => "Users", "sort_order" => 20],
        ];',
    );

    try {
        $registry = new ModuleRegistry($baseDir);
        $loader = new AdminMenuLoader($registry);
        $menu = new AdminMenu($loader);

        // User with user.manage and user.create permissions
        $user = new AdminUser(
            id: 1,
            email: 'editor@example.com',
            name: 'Editor',
            passwordHash: '',
            status: 'active',
            permissions: ['user.manage', 'user.create'],
        );

        $sections = $menu->sectionsFor($user);
        expect($sections)->toHaveCount(1)
            ->and($sections[0]->getId())->toBe('users')
            ->and($sections[0]->getMenuItems())->toHaveCount(1);

        $usersItem = $sections[0]->getMenuItems()[0];
        expect($usersItem->hasChildren())->toBeTrue()
            ->and($usersItem->getChildren())->toHaveCount(1)
            ->and($usersItem->getChildren()[0]->getId())->toBe('users-create');
    } finally {
        unlink($moduleDir . '/config/admin_menu.php');
        unlink($moduleDir . '/module.php');
        rmdir($moduleDir . '/config');
        rmdir($moduleDir);
        rmdir($baseDir . '/app');
        rmdir($baseDir);
    }
});

it('renders multi-layer navigation with parent active-branch awareness', function (): void {
    $child = new AdminMenuItem('pages-create', 'Add Page', '/admin/pages/create', 'page.manage', parent: 'pages', sortOrder: 10, group: 'Content');
    $parent = (new AdminMenuItem('pages', 'Pages', '/admin/pages', 'page.manage', sortOrder: 20, group: 'Content', icon: 'pages'))
        ->withChildren([$child]);

    $renderer = new AdminNavigationRenderer();
    $html = $renderer->render([
        new Zoosper\Admin\Navigation\AdminSection('content', 'Content', [$parent]),
    ], 'pages-create', '');

    expect($html)
        ->toContain('data-admin-section="content"')
        ->toContain('data-admin-item="pages"')
        ->toContain('class="active-parent" data-admin-active-branch="true"')
        ->toContain('data-admin-children-of="pages"')
        ->toContain('data-admin-item="pages-create"')
        ->toContain('class="admin-nav-sub-item active" aria-current="page"');
});
