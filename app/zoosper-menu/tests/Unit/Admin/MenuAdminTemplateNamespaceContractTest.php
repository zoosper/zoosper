<?php

declare(strict_types=1);

it('renders Admin templates through the menu module namespace', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Admin/MenuAdminResponder.php');

    expect($source)
        ->toContain("'zoosper-menu::admin/menu/index.latte'")
        ->toContain("'zoosper-menu::admin/menu/edit.latte'")
        ->not->toContain("'menu/admin/index.latte'")
        ->not->toContain("'menu/admin/edit.latte'");

    expect($root . '/resources/views/admin/menu/index.latte')->toBeFile()
        ->and($root . '/resources/views/admin/menu/edit.latte')->toBeFile();
});










