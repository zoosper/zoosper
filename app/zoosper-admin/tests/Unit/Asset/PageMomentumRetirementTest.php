<?php

declare(strict_types=1);

it('retires the obsolete Page Momentum menu and stylesheet', function (): void {
    $root = dirname(__DIR__, 5);
    $assets = (string) file_get_contents($root . '/app/zoosper-admin/config/admin_assets.php');
    $menu = (string) file_get_contents($root . '/app/zoosper-page/config/admin_menu.php');

    expect($assets)->not->toContain('page-momentum')
        ->and($menu)->not->toContain('Page momentum')
        ->and(is_file($root . '/app/zoosper-admin/resources/assets/admin/css/page-momentum.css'))->toBeFalse();
});
