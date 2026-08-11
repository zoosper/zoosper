<?php

declare(strict_types=1);

use Marko\Admin\Contracts\MenuItemInterface;
use Zoosper\Admin\Navigation\AdminMenu;

it('filters the live admin menu through the Marko permission contract', function (): void {
    $reflection = new ReflectionClass(AdminMenu::class);
    $method = $reflection->getMethod('itemsFor');
    $returnDoc = $method->getDocComment();
    $source = (string) file_get_contents($reflection->getFileName());

    expect($returnDoc)->toContain('list<MenuItemInterface>')
        ->and($source)->toContain('use Marko\\Admin\\Contracts\\MenuItemInterface;')
        ->toContain('static function (MenuItemInterface $item)')
        ->toContain('$item->getPermission()')
        ->not->toContain('$item->isAllowed(')
        ->not->toContain('$item->permission');
});

it('maps the Marko empty permission string to unrestricted sidebar access', function (): void {
    $root = dirname(__DIR__, 5);
    $itemSource = (string) file_get_contents(
        $root . '/app/zoosper-admin/src/Navigation/AdminMenuItem.php',
    );
    $menuSource = (string) file_get_contents(
        $root . '/app/zoosper-admin/src/Navigation/AdminMenu.php',
    );

    expect($itemSource)
        ->toContain('return $this->permission ??')
        ->and($menuSource)
        ->toContain("\$permission === '' || \$user->can(\$permission)");
});

it('keeps loading and presentation compatibility outside the filtering boundary', function (): void {
    $root = dirname(__DIR__, 5);
    $loader = (string) file_get_contents(
        $root . '/app/zoosper-admin/src/Navigation/AdminMenuLoader.php',
    );

    expect($loader)
        ->toContain('new AdminMenuItem(')
        ->toContain('group:')
        ->toContain('parent:')
        ->toContain('AdminPathCollectionTransformer');
});
