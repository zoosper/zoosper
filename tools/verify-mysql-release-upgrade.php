<?php

declare(strict_types=1);

use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Testing\Upgrade\MySqlUpgradeDatabaseWorkspace;
use Zoosper\Database\Migrator;

require dirname(__DIR__) . '/vendor/autoload.php';

if (PHP_SAPI !== 'cli' || trim((string) shell_exec('id -un')) !== 'vagrant') {
    throw new RuntimeException('MySQL release upgrade proof is CLI-only and must run as vagrant.');
}
foreach (['BR2D_MYSQL_HOST', 'BR2D_MYSQL_PORT', 'BR2D_MYSQL_USERNAME', 'BR2D_MYSQL_PASSWORD'] as $name) {
    if (getenv($name) === false || getenv($name) === '') {
        throw new RuntimeException($name . ' must be supplied explicitly for the disposable MySQL proof.');
    }
}
$releaseTag = $argv[1] ?? 'v0.3.1-alpha.1';
if (preg_match('/^v[0-9]+\.[0-9]+\.[0-9]+-alpha\.[0-9]+$/', $releaseTag) !== 1) {
    throw new RuntimeException('Release tag must be an explicit immutable alpha tag.');
}
$repository = realpath(dirname(__DIR__));
if ($repository === false) {
    throw new RuntimeException('Repository root could not be resolved.');
}
$host = (string) getenv('BR2D_MYSQL_HOST');
$port = (int) getenv('BR2D_MYSQL_PORT');
$username = (string) getenv('BR2D_MYSQL_USERNAME');
$password = (string) getenv('BR2D_MYSQL_PASSWORD');
$admin = mysqlConnection($host, $port, $username, $password);
$workspace = new MySqlUpgradeDatabaseWorkspace($admin);
$currentCommit = trim(run('git -C ' . q($repository) . ' rev-parse HEAD'));
$releaseCommit = trim(run('git -C ' . q($repository) . ' rev-list -n 1 ' . q($releaseTag)));
$root = sys_get_temp_dir() . '/zoosper-mysql-release-upgrade-' . bin2hex(random_bytes(8));
$release = $root . '/release';
$current = $root . '/current';
$mediaRoot = $root . '/media';
$composer = trim((string) shell_exec('command -v composer'));
if ($composer === '') {
    throw new RuntimeException('Composer is required.');
}
$result = null;
try {
    mkdir($root, 0700, true);
    run('git -C ' . q($repository) . ' worktree add --detach ' . q($release) . ' ' . q($releaseCommit));
    run('git -C ' . q($repository) . ' worktree add --detach ' . q($current) . ' ' . q($currentCommit));
    installDependencies($release, $composer);
    installDependencies($current, $composer);
    $result = $workspace->run(static function (string $database) use ($host, $port, $username, $password, $release, $current, $mediaRoot, $releaseTag, $releaseCommit, $currentCommit): array {
        $pdo = mysqlConnection($host, $port, $username, $password, $database);
        migrate($release, $pdo);
        applyForeignKeys($release, $database);
        seed($pdo, $mediaRoot);
        $before = snapshot($pdo, $mediaRoot);
        migrate($current, $pdo);
        applyForeignKeys($current, $database);
        $afterFirst = snapshot($pdo, $mediaRoot);
        migrate($current, $pdo);
        $afterSecond = snapshot($pdo, $mediaRoot);
        if ($before['fixtures'] !== $afterFirst['fixtures'] || $afterFirst !== $afterSecond) {
            throw new RuntimeException('MySQL release fixture identity or idempotent upgraded state changed.');
        }
        $orphans = orphanCount($pdo);
        if ($orphans !== 0) {
            throw new RuntimeException('MySQL fixture graph contains orphaned references after upgrade.');
        }
        $foreignKeys = foreignKeyCount($pdo);
        if ($foreignKeys < 1) {
            throw new RuntimeException('No live MySQL foreign keys were found after upgrade.');
        }
        return [
            'driver' => 'mysql',
            'release_tag' => $releaseTag,
            'release_commit' => $releaseCommit,
            'current_commit' => $currentCommit,
            'fixture_tables' => array_keys($before['fixtures']),
            'migration_count_before' => count($before['migrations']),
            'migration_count_after' => count($afterSecond['migrations']),
            'live_foreign_keys' => $foreignKeys,
            'orphaned_references' => 0,
            'idempotent' => true,
            'database_created' => true,
        ];
    });
    if (!$workspace->cleanupCompleted()) {
        throw new RuntimeException('Disposable MySQL database still exists after upgrade-proof cleanup.');
    }
    $result['database_dropped'] = true;
    run('php8.5 ' . q($current . '/bin/zoosper') . ' compile', $current);
    run('php8.5 ' . q($current . '/bin/zoosper') . ' module:manifest:check', $current);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), PHP_EOL;
} finally {
    removeWorktree($repository, $release);
    removeWorktree($repository, $current);
    removeTree($root);
}

function installDependencies(string $worktree, string $composer): void
{
    $command = 'COMPOSER_MAX_PARALLEL_HTTP=1 php8.5 ' . q($composer) . ' install --no-dev --no-interaction --prefer-dist --no-progress';
    $last = null;
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        try { run($command, $worktree); return; } catch (RuntimeException $exception) { $last = $exception; if ($attempt < 3) { sleep($attempt); } }
    }
    throw new RuntimeException('Unable to install locked runtime dependencies after three bounded attempts.', previous: $last);
}

function mysqlConnection(string $host, int $port, string $username, string $password, ?string $database = null): PDO
{
    $dsn = sprintf('mysql:host=%s;port=%d;%scharset=utf8mb4', $host, $port, $database === null ? '' : 'dbname=' . $database . ';');
    return new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
}

function migrate(string $worktree, PDO $pdo): void
{
    (new Migrator($pdo, $worktree, new ModuleRegistry($worktree)))->migrate();
}

function applyForeignKeys(string $worktree, string $database): void
{
    putenv('DB_CONNECTION=mysql'); putenv('DB_DRIVER=mysql'); putenv('DB_HOST=' . getenv('BR2D_MYSQL_HOST')); putenv('DB_PORT=' . getenv('BR2D_MYSQL_PORT'));
    putenv('DB_DATABASE=' . $database); putenv('DB_USERNAME=' . getenv('BR2D_MYSQL_USERNAME')); putenv('DB_PASSWORD=' . getenv('BR2D_MYSQL_PASSWORD'));
    run('php8.5 ' . q($worktree . '/bin/zoosper') . ' schema:foreign-keys:apply --confirm=apply', $worktree);
}

function seed(PDO $pdo, string $mediaRoot): void
{
    mkdir($mediaRoot . '/storage/media/original', 0700, true);
    file_put_contents($mediaRoot . '/storage/media/original/br2d.txt', 'zoosper-br2d-mysql-media-fixture');
    $now = '2026-09-09 00:00:00';
    insert($pdo, 'admin_roles', ['code' => 'br2d_role', 'label' => 'BR-2D Role', 'created_at' => $now, 'updated_at' => $now]);
    $role = id($pdo, 'admin_roles', 'code', 'br2d_role');
    insert($pdo, 'admin_users', ['email' => 'br2d@example.test', 'name' => 'BR-2D Admin', 'password_hash' => password_hash('UpgradeProof123!', PASSWORD_DEFAULT), 'status' => 'active', 'created_at' => $now, 'updated_at' => $now]);
    $user = id($pdo, 'admin_users', 'email', 'br2d@example.test');
    insert($pdo, 'admin_user_roles', ['user_id' => $user, 'role_id' => $role]);
    insert($pdo, 'sites', ['code' => 'br2d', 'name' => 'BR-2D Site', 'status' => 'active', 'homepage_slug' => 'upgrade-proof', 'created_at' => $now, 'updated_at' => $now]);
    $site = id($pdo, 'sites', 'code', 'br2d');
    insert($pdo, 'site_domains', ['site_id' => $site, 'host' => 'br2d.example.test', 'is_primary' => 1, 'created_at' => $now, 'updated_at' => $now]);
    insert($pdo, 'media_assets', ['uuid' => 'br2d-media', 'filename' => 'br2d.txt', 'original_filename' => 'br2d.txt', 'mime_type' => 'text/plain', 'extension' => 'txt', 'size_bytes' => 32, 'storage_path' => 'storage/media/original/br2d.txt', 'public_path' => '/media/br2d.txt', 'status' => 'active', 'created_by' => $user, 'created_at' => $now, 'updated_at' => $now]);
    insert($pdo, 'pages', ['site_id' => $site, 'title' => 'Upgrade proof', 'slug' => 'upgrade-proof', 'content' => '<p>BR-2D /media/br2d.txt</p>', 'status' => 'published', 'created_by' => $user, 'updated_by' => $user, 'created_at' => $now, 'updated_at' => $now]);
    $page = id($pdo, 'pages', 'slug', 'upgrade-proof');
    insert($pdo, 'page_revisions', ['page_id' => $page, 'title' => 'Upgrade proof', 'content' => '<p>BR-2D /media/br2d.txt</p>', 'created_by' => $user, 'created_at' => $now]);
    insert($pdo, 'menus', ['site_id' => $site, 'code' => 'br2d', 'label' => 'BR-2D Menu', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now]);
    $menu = id($pdo, 'menus', 'code', 'br2d');
    insert($pdo, 'menu_items', ['menu_id' => $menu, 'page_id' => $page, 'label' => 'Upgrade proof', 'target' => '_self', 'position' => 10, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now]);
}

function insert(PDO $pdo, string $table, array $values): void
{
    $statement = $pdo->prepare('SELECT COLUMN_NAME, IS_NULLABLE, COLUMN_DEFAULT, EXTRA, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table ORDER BY ORDINAL_POSITION');
    $statement->execute(['table' => $table]);
    $columns = $statement->fetchAll();
    if ($columns === []) { throw new RuntimeException('Required release table is missing: ' . $table); }
    $row = $values;
    foreach ($columns as $column) {
        $name = (string) $column['COLUMN_NAME'];
        if (str_contains((string) $column['EXTRA'], 'auto_increment') || array_key_exists($name, $row) || $column['IS_NULLABLE'] === 'YES' || $column['COLUMN_DEFAULT'] !== null) { continue; }
        $row[$name] = in_array((string) $column['DATA_TYPE'], ['int','bigint','smallint','tinyint'], true) ? 0 : (str_ends_with($name, '_at') ? '2026-09-09 00:00:00' : 'br2d');
    }
    $names = array_keys($row);
    $pdo->prepare('INSERT INTO `' . $table . '` (`' . implode('`, `', $names) . '`) VALUES (:' . implode(', :', $names) . ')')->execute($row);
}

function snapshot(PDO $pdo, string $mediaRoot): array
{
    $fixtures = [];
    foreach (['admin_roles','admin_users','admin_user_roles','sites','site_domains','media_assets','pages','page_revisions','menus','menu_items'] as $table) {
        $fixtures[$table] = hash('sha256', json_encode($pdo->query('SELECT * FROM `' . $table . '` ORDER BY 1')->fetchAll(), JSON_THROW_ON_ERROR));
    }
    $fixtures['media_file'] = hash_file('sha256', $mediaRoot . '/storage/media/original/br2d.txt');
    return ['fixtures' => $fixtures, 'migrations' => $pdo->query('SELECT * FROM migrations ORDER BY 1')->fetchAll()];
}

function orphanCount(PDO $pdo): int
{
    $queries = [
        'SELECT COUNT(*) FROM admin_user_roles c LEFT JOIN admin_users p ON p.id=c.user_id WHERE p.id IS NULL',
        'SELECT COUNT(*) FROM admin_user_roles c LEFT JOIN admin_roles p ON p.id=c.role_id WHERE p.id IS NULL',
        'SELECT COUNT(*) FROM site_domains c LEFT JOIN sites p ON p.id=c.site_id WHERE p.id IS NULL',
        'SELECT COUNT(*) FROM pages c LEFT JOIN sites p ON p.id=c.site_id WHERE p.id IS NULL',
        'SELECT COUNT(*) FROM page_revisions c LEFT JOIN pages p ON p.id=c.page_id WHERE p.id IS NULL',
        'SELECT COUNT(*) FROM menus c LEFT JOIN sites p ON p.id=c.site_id WHERE p.id IS NULL',
        'SELECT COUNT(*) FROM menu_items c LEFT JOIN menus p ON p.id=c.menu_id WHERE p.id IS NULL',
    ];
    return array_sum(array_map(static fn (string $sql): int => (int) $pdo->query($sql)->fetchColumn(), $queries));
}

function foreignKeyCount(PDO $pdo): int
{
    return (int) $pdo->query("SELECT COUNT(DISTINCT CONSTRAINT_NAME) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchColumn();
}
function id(PDO $pdo, string $table, string $column, string $value): int { $s=$pdo->prepare('SELECT id FROM `' . $table . '` WHERE `' . $column . '`=:value'); $s->execute(['value'=>$value]); $id=$s->fetchColumn(); if($id===false){throw new RuntimeException('Fixture ID not found: '.$table);} return (int)$id; }
function run(string $command, ?string $cwd=null): string { $prefix=$cwd===null?'':'cd '.q($cwd).' && '; exec($prefix.$command.' 2>&1',$output,$code); if($code!==0){throw new RuntimeException("Command failed: {$command}\n".implode("\n",$output));} return implode("\n",$output); }
function q(string $value): string { return escapeshellarg($value); }
function removeWorktree(string $repository,string $path): void { if(is_dir($path)){exec('git -C '.q($repository).' worktree remove --force '.q($path).' >/dev/null 2>&1');} }
function removeTree(string $path): void { if($path!==''&&is_dir($path)){exec('rm -rf '.q($path));} }
