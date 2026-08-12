<?php

declare(strict_types=1);

it('mirrors migration-owned foreign-key semantics in declarative schemas', function (): void {
    $root = dirname(__DIR__, 5);
    $site = require $root . '/app/zoosper-site/config/db_schema.php';
    $menu = require $root . '/app/zoosper-menu/config/db_schema.php';
    $auth = require $root . '/app/zoosper-auth/config/db_schema.php';

    expect($site['tables']['site_domains']['foreign_keys']['fk_site_domains_site']['on_delete'])->toBe('CASCADE')
        ->and($menu['tables']['menus']['foreign_keys']['fk_menus_site']['on_delete'])->toBe('CASCADE')
        ->and($menu['tables']['menu_items']['foreign_keys']['fk_menu_items_menu']['on_delete'])->toBe('CASCADE')
        ->and($menu['tables']['menu_items']['foreign_keys']['fk_menu_items_parent']['on_delete'])->toBe('CASCADE')
        ->and($menu['tables']['menu_items']['foreign_keys']['fk_menu_items_page']['on_delete'])->toBe('SET NULL')
        ->and($auth['tables']['admin_user_roles']['foreign_keys'])->toHaveCount(2)
        ->and($auth['tables']['admin_role_permissions']['foreign_keys'])->toHaveCount(2);
});
