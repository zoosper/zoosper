<?php

declare(strict_types=1);

use Marko\Admin\Contracts\MenuItemInterface;
use Zoosper\Admin\Navigation\AdminMenuItem;

it('makes the existing Zoosper item a native Marko admin menu item', function (): void {
    $item = new AdminMenuItem(
        code: 'pages',
        label: 'Pages',
        url: '/admin/pages',
        permission: 'page.manage',
        parent: null,
        sortOrder: 20,
        group: 'Content',
        icon: 'file-text',
    );

    expect($item)->toBeInstanceOf(MenuItemInterface::class)
        ->and($item->getId())->toBe('pages')
        ->and($item->getLabel())->toBe('Pages')
        ->and($item->getUrl())->toBe('/admin/pages')
        ->and($item->getPermission())->toBe('page.manage')
        ->and($item->getSortOrder())->toBe(20)
        ->and($item->getIcon())->toBe('file-text');
});

it('preserves existing public properties grouping parenting and ACL behaviour', function (): void {
    $item = new AdminMenuItem('pages', 'Pages', '/admin/pages', 'page.manage', 'content', 20, 'Content');
    $unrestricted = new AdminMenuItem('dashboard', 'Dashboard', '/admin');

    expect($item->code)->toBe('pages')
        ->and($item->label)->toBe('Pages')
        ->and($item->url)->toBe('/admin/pages')
        ->and($item->parent)->toBe('content')
        ->and($item->getParent())->toBe('content')
        ->and($item->group)->toBe('Content')
        ->and($item->getGroup())->toBe('Content')
        ->and($item->hasChildren())->toBeFalse()
        ->and($item->getChildren())->toBe([])
        ->and($unrestricted->permission)->toBeNull()
        ->and($unrestricted->getPermission())->toBe('')
        ->and($unrestricted->isAllowed(static fn (): bool => false))->toBeTrue()
        ->and($item->isAllowed(static fn (string $permission): bool => $permission === 'page.manage'))->toBeTrue()
        ->and($item->isAllowed(static fn (): bool => false))->toBeFalse();

    $child = new AdminMenuItem('pages-create', 'New Page', '/admin/pages/create', 'page.manage', parent: 'pages');
    $itemWithChildren = $item->withChildren([$child]);

    expect($itemWithChildren->hasChildren())->toBeTrue()
        ->and($itemWithChildren->getChildren())->toHaveCount(1)
        ->and($itemWithChildren->getChildren()[0]->getId())->toBe('pages-create');
});

it('declares the Marko contract dependency in the owning Composer package', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode(
        (string) file_get_contents($root . '/app/zoosper-admin/composer.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    expect($composer['require']['marko/admin'] ?? null)->toBe('0.8.5');
});










