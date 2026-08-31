<?php

declare(strict_types=1);

use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\Contracts\AdminSectionRegistryInterface;
use Marko\Admin\Contracts\MenuItemInterface;
use Marko\Admin\MenuItem;

it('installs the stable Marko admin contracts required for a later adapter', function (): void {
    expect(class_exists(MenuItem::class))->toBeTrue()
        ->and(interface_exists(MenuItemInterface::class))->toBeTrue()
        ->and(interface_exists(AdminSectionInterface::class))->toBeTrue()
        ->and(interface_exists(AdminSectionRegistryInterface::class))->toBeTrue();

    $item = new ReflectionClass(MenuItemInterface::class);
    foreach (['getId', 'getLabel', 'getUrl', 'getIcon', 'getSortOrder', 'getPermission'] as $method) {
        expect($item->hasMethod($method))->toBeTrue();
    }
});

it('retains Zoosper admin navigation as the active runtime during the foundation phase', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($composer['require']['marko/admin'] ?? null)->toBe('0.8.5')
        ->and($root . '/app/zoosper-admin/src/Navigation/AdminMenuItem.php')->toBeFile()
        ->and($root . '/app/zoosper-admin/src/Navigation/AdminMenuLoader.php')->toBeFile()
        ->and($root . '/app/zoosper-admin/src/Navigation/AdminMenu.php')->toBeFile();
});

it('does not install Marko admin panel or authentication implementations yet', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($composer['require'] ?? [])
        ->not->toHaveKey('marko/admin-panel')
        ->not->toHaveKey('marko/admin-auth')
        ->not->toHaveKey('marko/admin-api');
});










