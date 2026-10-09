<?php

declare(strict_types=1);

use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\Contracts\AdminSectionRegistryInterface;
use Marko\Admin\Contracts\MenuItemInterface;
use Marko\Admin\Discovery\AdminSectionDefinition;
use Zoosper\Admin\Navigation\{AdminMenu, AdminMenuItem, AdminSectionBuilder, AdminSectionRegistry};

it('publishes interface-typed loader items with icon compatibility', function (): void {
    $root = dirname(__DIR__, 5);
    $loader = (string) file_get_contents($root . '/app/zoosper-admin/src/Navigation/AdminMenuLoader.php');
    expect($loader)->toContain('@return list<MenuItemInterface>')->toContain("icon: (string) (\$item['icon'] ?? '')");
});

it('groups existing items into deterministic Marko admin sections', function (): void {
    $sections = (new AdminSectionBuilder())->build([
        new AdminMenuItem('roles', 'Roles', '/admin/roles', 'role.manage', group: 'Users'),
        new AdminMenuItem('pages', 'Pages', '/admin/pages', 'page.manage', group: 'Content'),
        new AdminMenuItem('menus', 'Menus', '/admin/menus', 'menu.manage', group: 'Content'),
    ]);
    expect($sections)->toHaveCount(2)
        ->and($sections[0])->toBeInstanceOf(AdminSectionInterface::class)
        ->and($sections[0]->getLabel())->toBe('Users')
        ->and($sections[1]->getLabel())->toBe('Content')
        ->and($sections[1]->getMenuItems())->toHaveCount(2);
    foreach ($sections as $section) foreach ($section->getMenuItems() as $item) expect($item)->toBeInstanceOf(MenuItemInterface::class);
});

it('implements the Marko registry with loud lookup and stable replacement', function (): void {
    $registry = new AdminSectionRegistry();
    expect($registry)->toBeInstanceOf(AdminSectionRegistryInterface::class);

    $first = new \Zoosper\Admin\Navigation\AdminSection('content', 'Content', [], sortOrder: 20);
    $replacement = new \Zoosper\Admin\Navigation\AdminSection('content', 'Content replacement', [], sortOrder: 10);
    $registry->register($first);
    $registry->register($replacement);

    expect($registry->get('content'))->toBe($replacement)
        ->and($registry->all())->toBe([$replacement])
        ->and(fn () => $registry->get('missing'))->toThrow(\Marko\Admin\Exceptions\AdminException::class, 'not found');

    $source = (string) file_get_contents((new ReflectionClass(AdminMenu::class))->getFileName());
    expect($source)->toContain('list<AdminSectionInterface>')->toContain('sectionsFor(')->toContain('$this->sections->build($this->itemsFor($user))');
});

it('supports lazy Marko section definitions without changing direct registration', function (): void {
    $builds = 0;
    $registry = new AdminSectionRegistry(static function (string $className) use (&$builds): object {
        $builds++;

        return new $className('lazy', 'Lazy section', [], sortOrder: 5);
    });
    $registry->registerDefinition(new AdminSectionDefinition(
        \Zoosper\Admin\Navigation\AdminSection::class,
        'lazy',
        'Lazy section',
        '',
        5,
    ));

    expect($builds)->toBe(0)
        ->and($registry->get('lazy'))->toBeInstanceOf(\Zoosper\Admin\Navigation\AdminSection::class)
        ->and($builds)->toBe(1)
        ->and($registry->get('lazy')->getId())->toBe('lazy')
        ->and($builds)->toBe(1);
});
