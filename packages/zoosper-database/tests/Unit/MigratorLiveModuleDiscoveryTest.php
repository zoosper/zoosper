<?php

declare(strict_types=1);

namespace Zoosper\Database\Tests;

use PDO;
use Zoosper\Database\Migrator;
use Zoosper\Core\Module\ModuleRegistry;

function staleCompiledMigrationFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-stale-migration-' . bin2hex(random_bytes(6));
    $module = $root . '/app/acme-fresh';

    mkdir($root . '/database/migrations', 0775, true);
    mkdir($root . '/var/cache', 0775, true);
    mkdir($module . '/database/migrations', 0775, true);

    // Simulates the valid compiled manifest left by the previous release,
    // before the new module was installed.
    file_put_contents($root . '/var/cache/modules.php', "<?php\nreturn [];\n");

    file_put_contents(
        $module . '/composer.json',
        json_encode([
            'name' => 'acme/fresh',
            'type' => 'zoosper-module',
            'extra' => ['marko' => ['module' => true]],
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
    );
    file_put_contents($module . '/module.php', "<?php\nreturn [];\n");
    file_put_contents(
        $module . '/database/migrations/209901010000_create_fresh_module_table.php',
        <<<'PHP'
<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $pdo->exec('CREATE TABLE fresh_module_proof (id INTEGER PRIMARY KEY)');
};
PHP,
    );

    return $root;
}

test('migrations discover a newly installed module despite a stale compiled manifest', function (): void {
    $root = staleCompiledMigrationFixture();
    $registry = new ModuleRegistry($root);

    // Phase 8C rejects the previous release's stale compiled manifest, so
    // ordinary runtime discovery immediately sees the newly installed module.
    expect(array_map(static fn ($module) => $module->name, $registry->enabledModules()))
        ->toContain('acme-fresh');

    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    (new Migrator($pdo, $root . '/database/migrations', $registry))->migrate();

    $table = $pdo->query(
        "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'fresh_module_proof'",
    )->fetchColumn();

    expect($table)->toBe('fresh_module_proof');
    expect((int) $pdo->query(
        "SELECT COUNT(*) FROM migrations WHERE migration = '209901010000_create_fresh_module_table.php'",
    )->fetchColumn())->toBe(1);
});

test('migration discovery sorts root and module files by filename and handles empty directories', function (): void {
    $root = staleCompiledMigrationFixture();
    $registry = new ModuleRegistry($root);
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    file_put_contents($root . '/database/migrations/209901010001_create_order.php', "<?php return 'CREATE TABLE migration_order (value INTEGER)';");
    file_put_contents($root . '/app/acme-fresh/database/migrations/209901010002_insert_order.php', "<?php return 'INSERT INTO migration_order VALUES (1)';");
    file_put_contents($root . '/database/migrations/209901010003_insert_order.php', "<?php return 'INSERT INTO migration_order VALUES (2)';");

    (new Migrator($pdo, $root, $registry))->migrate();
    expect($pdo->query('SELECT value FROM migration_order ORDER BY rowid')->fetchAll(PDO::FETCH_COLUMN))->toBe([1, 2]);

    $emptyRoot = $root . '/empty';
    mkdir($emptyRoot . '/database/migrations', 0775, true);
    (new Migrator(new PDO('sqlite::memory:'), $emptyRoot, new ModuleRegistry($emptyRoot)))->migrate();
});











