<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use PDO;
use Zoosper\Core\Database\MigrationInterface;

it('seeds Store Orders permissions idempotently with ACL tree metadata', function (): void {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite is not available.');
    }

    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec(
        'CREATE TABLE admin_permissions ('
        . 'id INTEGER PRIMARY KEY AUTOINCREMENT, '
        . 'code TEXT NOT NULL UNIQUE, '
        . 'label TEXT NOT NULL, '
        . 'created_at TEXT NOT NULL, '
        . 'parent_code TEXT NULL, '
        . 'sort_order INTEGER NOT NULL DEFAULT 100)'
    );

    $root = dirname(__DIR__, 4);
    $migration = require $root
        . '/packages/zoosper-store-orders/database/migrations/'
        . '202608020001_seed_store_order_permissions.php';

    expect($migration)->toBeInstanceOf(MigrationInterface::class);
    $migration->up($pdo, 'sqlite');
    $migration->up($pdo, 'sqlite');

    $rows = $pdo->query(
        "SELECT code, label, parent_code, sort_order "
        . "FROM admin_permissions WHERE code LIKE 'store_order.%' ORDER BY sort_order"
    )->fetchAll();

    expect($rows)->toBe([
        [
            'code' => 'store_order.view',
            'label' => 'View store orders',
            'parent_code' => 'content',
            'sort_order' => 30,
        ],
        [
            'code' => 'store_order.export',
            'label' => 'Export store orders',
            'parent_code' => 'content',
            'sort_order' => 40,
        ],
    ]);
});

it('does not misuse module ACL config as a permission persistence mechanism', function (): void {
    $root = dirname(__DIR__, 4);
    expect(require $root . '/packages/zoosper-store-orders/config/acl.php')->toBe([]);
});
