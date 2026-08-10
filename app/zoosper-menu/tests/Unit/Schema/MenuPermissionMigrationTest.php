<?php

declare(strict_types=1);

use Zoosper\Core\Database\Migrator;
use Zoosper\Core\Module\ModuleRegistry;

it('seeds menu management permission for default content roles', function (): void {
    $root = dirname(__DIR__, 5);
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');

    (new Migrator($pdo, $root, new ModuleRegistry($root)))->migrate();

    $permission = $pdo->query("SELECT id FROM admin_permissions WHERE code = 'menu.manage'")->fetchColumn();
    expect($permission)->not->toBeFalse();

    $statement = $pdo->prepare(
        "SELECT r.code FROM admin_role_permissions rp JOIN admin_roles r ON r.id = rp.role_id WHERE rp.permission_id = :permission ORDER BY r.code",
    );
    $statement->execute(['permission' => (int) $permission]);

    expect($statement->fetchAll(PDO::FETCH_COLUMN))->toBe(['content_admin', 'super_admin']);
});
