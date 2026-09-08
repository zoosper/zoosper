<?php

declare(strict_types=1);

use Zoosper\Database\MigrationInventory;

it('requires globally unique migration basenames in the repository', function (): void {
    $root = dirname(__DIR__, 4);
    $inventory = (new MigrationInventory())->discover($root);

    expect($inventory)->not->toBeEmpty();
    foreach ($inventory as $basename => $path) {
        expect($basename)->toBe(basename($path));
    }
});

it('fails closed when two modules declare the same migration basename', function (): void {
    $root = sys_get_temp_dir() . '/zoosper-migration-inventory-' . bin2hex(random_bytes(6));
    $a = $root . '/app/alpha/database/migrations';
    $b = $root . '/packages/beta/database/migrations';
    mkdir($a, 0775, true);
    mkdir($b, 0775, true);
    $name = '209901010001_duplicate.php';
    file_put_contents($a . '/' . $name, "<?php\nreturn [];\n");
    file_put_contents($b . '/' . $name, "<?php\nreturn [];\n");

    try {
        expect(fn (): array => (new MigrationInventory())->discover($root))
            ->toThrow(RuntimeException::class, 'Duplicate migration basename');
    } finally {
        exec('rm -rf ' . escapeshellarg($root));
    }
});
