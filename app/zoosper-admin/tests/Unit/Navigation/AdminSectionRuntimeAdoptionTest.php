<?php

declare(strict_types=1);

use Zoosper\Admin\Navigation\AdminMenuItem;
use Zoosper\Admin\Navigation\AdminNavigationRenderer;
use Zoosper\Admin\Navigation\AdminSection;

it('renders Marko sections through the live admin navigation presentation', function (): void {
    $renderer = new AdminNavigationRenderer();
    $childItem = new AdminMenuItem('pages-create', 'New Page', '/control/pages/create', 'page.manage', parent: 'pages', sortOrder: 10, group: 'Content');
    $parentItem = (new AdminMenuItem('pages', 'Pages', '/control/pages', 'page.manage', sortOrder: 20, group: 'Content'))->withChildren([$childItem]);

    $html = $renderer->render([
        new AdminSection('content', 'Content', [
            new AdminMenuItem('dashboard', 'Dashboard', '/control', 'admin.access', sortOrder: 10, group: 'Content', icon: 'home'),
            $parentItem,
        ]),
    ], 'pages-create', '<div class="logout-marker">Logout</div>');

    expect($html)
        ->toContain('aria-label="Admin navigation"')
        ->toContain('data-admin-section="content"')
        ->toContain('data-admin-item="pages"')
        ->toContain('data-admin-label="Pages"')
        ->toContain('title="Pages"')
        ->toContain('href="/control/pages"')
        ->toContain('class="active-parent" data-admin-active-branch="true"')
        ->toContain('class="admin-nav-children" data-admin-children-of="pages"')
        ->toContain('data-admin-item="pages-create"')
        ->toContain('data-admin-label="New Page"')
        ->toContain('class="admin-nav-sub-item active" aria-current="page"')
        ->toContain('data-admin-icon="home"')
        ->toContain('logout-marker');
});

it('escapes section item and icon content and skips empty sections', function (): void {
    $renderer = new AdminNavigationRenderer();
    $html = $renderer->render([
        new AdminSection('empty', 'Empty', []),
        new AdminSection('unsafe', '<Content>', [
            new AdminMenuItem('item', '<Item>', '/admin?x=1&y=2', icon: '<svg>'),
        ]),
    ], '', '');

    expect($html)
        ->not->toContain('data-admin-section="empty"')
        ->toContain('&lt;Content&gt;')
        ->toContain('&lt;Item&gt;')
        ->toContain('data-admin-icon="&lt;svg&gt;"')
        ->toContain('data-admin-label="&lt;Item&gt;"')
        ->toContain('title="&lt;Item&gt;"')
        ->toContain('/admin?x=1&amp;y=2')
        ->not->toContain('<svg>');
});

it('cuts the live AdminLayout over from flat items to Marko sections', function (): void {
    $root = dirname(__DIR__, 5);
    $layout = (string) file_get_contents($root . '/app/zoosper-admin/src/Layout/AdminLayout.php');
    $services = (string) file_get_contents($root . '/app/zoosper-admin/config/services.php');

    expect($layout)
        ->toContain('$this->menu->sectionsFor($user)')
        ->toContain('$this->navigationRenderer->render(')
        ->not->toContain('itemsFor($user)')
        ->not->toContain('navigationLink(')
        ->not->toContain('AdminMenuItem')
        ->and($services)
        ->toContain('AdminNavigationRenderer::class')
        ->toContain('$services->get(AdminNavigationRenderer::class)');
});
