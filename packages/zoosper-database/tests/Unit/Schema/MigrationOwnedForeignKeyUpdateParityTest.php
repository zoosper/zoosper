<?php

declare(strict_types=1);

use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Database\Migrator;
use Zoosper\Database\Schema\SchemaLoader;
use Zoosper\Database\Schema\SchemaForeignKeyReconciliationService;

/** Prove migration-owned actions through the real loader and fresh in-memory migrations. */
it('preserves NO ACTION updates and reconciles fresh and repeat migrations', function (): void {
    $root = dirname(__DIR__, 5);
    $modules = new ModuleRegistry($root);
    $loader = new SchemaLoader($modules);
    $tables = $loader->load()->tables();
    $expected = [
        'admin_user_roles' => ['fk_admin_user_roles_user', 'fk_admin_user_roles_role'],
        'admin_role_permissions' => ['fk_admin_role_permissions_role', 'fk_admin_role_permissions_permission'],
        'menus' => ['fk_menus_site'],
        'menu_items' => ['fk_menu_items_menu', 'fk_menu_items_parent', 'fk_menu_items_page'],
        'site_domains' => ['fk_site_domains_site'],
        'page_revisions' => ['fk_page_revisions_page'],
    ];
    foreach ($expected as $table => $names) {
        foreach ($names as $name) {
            expect($tables[$table]->foreignKeys[$name]->onUpdate)->toBe('NO ACTION');
        }
    }
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $migrator = new Migrator($pdo, $root . '/database/migrations', $modules);
    $service = new SchemaForeignKeyReconciliationService($pdo, 'sqlite', $loader);
    $migrator->migrate();
    $count = (int) $pdo->query('SELECT COUNT(*) FROM migrations')->fetchColumn();
    for ($iteration = 0; $iteration < 2; ++$iteration) {
        expect($service->counts($service->plan()))->toBe([
            'present' => 36,
            'add' => 0,
            'mismatch' => 0,
            'sqlite_rebuild_required' => 0,
        ]);
        expect($pdo->query('PRAGMA foreign_key_check')->fetchAll(PDO::FETCH_ASSOC))->toBe([]);
        $migrator->migrate();
        expect((int) $pdo->query('SELECT COUNT(*) FROM migrations')->fetchColumn())->toBe($count);
    }
});
