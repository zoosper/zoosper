<?php

declare(strict_types=1);

use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Database\Migrator;

require dirname(__DIR__) . '/vendor/autoload.php';

if (PHP_SAPI !== 'cli') {
    throw new RuntimeException('Release upgrade proof is CLI-only.');
}
if (get_current_user() !== 'vagrant' && trim((string) shell_exec('id -un')) !== 'vagrant') {
    throw new RuntimeException('Release upgrade proof must run as vagrant.');
}

$repository = realpath(dirname(__DIR__));
if ($repository === false) {
    throw new RuntimeException('Repository root could not be resolved.');
}
$releaseTag = $argv[1] ?? 'v0.3.1-alpha.1';
if (preg_match('/^v[0-9]+\.[0-9]+\.[0-9]+-alpha\.[0-9]+$/', $releaseTag) !== 1) {
    throw new RuntimeException('Release tag must be an explicit immutable alpha tag.');
}
$currentCommit = trim(run('git -C ' . q($repository) . ' rev-parse HEAD'));
$releaseCommit = trim(run('git -C ' . q($repository) . ' rev-list -n 1 ' . q($releaseTag)));
$root = sys_get_temp_dir() . '/zoosper-release-upgrade-' . bin2hex(random_bytes(8));
$release = $root . '/release';
$current = $root . '/current';
$database = $root . '/upgrade.sqlite';
$mediaRoot = $root . '/media';
$composer = trim((string) shell_exec('command -v composer'));
if ($composer === '') {
    throw new RuntimeException('Composer is required.');
}

try {
    mkdir($root, 0700, true);
    run('git -C ' . q($repository) . ' worktree add --detach ' . q($release) . ' ' . q($releaseCommit));
    run('git -C ' . q($repository) . ' worktree add --detach ' . q($current) . ' ' . q($currentCommit));
    install($release, $composer);
    migrate($release, $database);
    seed($database, $mediaRoot);
    $before = snapshot($database, $mediaRoot);

    install($current, $composer);
    migrate($current, $database);
    $afterFirst = snapshot($database, $mediaRoot);
    migrate($current, $database);
    $afterSecond = snapshot($database, $mediaRoot);

    if ($before['fixtures'] !== $afterFirst['fixtures'] || $afterFirst !== $afterSecond) {
        throw new RuntimeException('Release fixture identity or idempotent upgraded state changed.');
    }
    $pdo = connection($database);
    $violations = $pdo->query('PRAGMA foreign_key_check')->fetchAll(PDO::FETCH_ASSOC);
    if ($violations !== []) {
        throw new RuntimeException('SQLite foreign-key violations remain after upgrade.');
    }
    run('php8.5 ' . q($current . '/bin/zoosper') . ' compile', $current);
    run('php8.5 ' . q($current . '/bin/zoosper') . ' module:manifest:check', $current);

    echo json_encode([
        'release_tag' => $releaseTag,
        'release_commit' => $releaseCommit,
        'current_commit' => $currentCommit,
        'fixture_tables' => array_keys($before['fixtures']),
        'migration_count_before' => count($before['migrations']),
        'migration_count_after' => count($afterSecond['migrations']),
        'foreign_key_violations' => 0,
        'idempotent' => true,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
} finally {
    removeWorktree($repository, $release);
    removeWorktree($repository, $current);
    removeTree($root);
}

function install(string $worktree, string $composer): void
{
    $command = 'COMPOSER_MAX_PARALLEL_HTTP=1 php8.5 ' . q($composer)
        . ' install --no-dev --no-interaction --prefer-dist --no-progress';
    $lastError = null;
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        try {
            run($command, $worktree);
            return;
        } catch (RuntimeException $exception) {
            $lastError = $exception;
            if ($attempt < 3) {
                sleep($attempt);
            }
        }
    }
    throw new RuntimeException(
        'Unable to install locked runtime dependencies after three bounded attempts.',
        previous: $lastError,
    );
}

function migrate(string $worktree, string $database): void
{
    $script = <<<'CODE'
<?php
require $argv[1] . '/vendor/autoload.php';
$pdo = new PDO('sqlite:' . $argv[2]);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->exec('PRAGMA foreign_keys = ON');
(new Zoosper\Database\Migrator($pdo, $argv[1], new Zoosper\Core\Module\ModuleRegistry($argv[1])))->migrate();
CODE;
    $runner = dirname($database) . '/migrate-' . basename($worktree) . '.php';
    file_put_contents($runner, $script);
    run('php8.5 ' . q($runner) . ' ' . q($worktree) . ' ' . q($database), $worktree);
}

function seed(string $database, string $mediaRoot): void
{
    $pdo = connection($database);
    mkdir($mediaRoot . '/storage/media/original', 0700, true);
    file_put_contents($mediaRoot . '/storage/media/original/br2b.txt', 'zoosper-br2b-media-fixture');
    insert($pdo, 'admin_roles', ['code' => 'br2b_role', 'label' => 'BR-2B Role']);
    $role = id($pdo, 'admin_roles', 'code', 'br2b_role');
    insert($pdo, 'admin_users', ['email' => 'br2b@example.test', 'name' => 'BR-2B Admin', 'password_hash' => password_hash('UpgradeProof123!', PASSWORD_DEFAULT), 'status' => 'active']);
    $user = id($pdo, 'admin_users', 'email', 'br2b@example.test');
    insert($pdo, 'admin_user_roles', ['user_id' => $user, 'role_id' => $role]);
    insert($pdo, 'sites', ['code' => 'br2b', 'name' => 'BR-2B Site', 'status' => 'active', 'homepage_slug' => 'upgrade-proof']);
    $site = id($pdo, 'sites', 'code', 'br2b');
    insert($pdo, 'site_domains', ['site_id' => $site, 'host' => 'br2b.example.test', 'is_primary' => 1]);
    insert($pdo, 'media_assets', ['uuid' => 'br2b-media', 'filename' => 'br2b.txt', 'original_filename' => 'br2b.txt', 'mime_type' => 'text/plain', 'extension' => 'txt', 'size_bytes' => 27, 'storage_path' => 'storage/media/original/br2b.txt', 'public_path' => '/media/br2b.txt', 'status' => 'active', 'created_by' => $user]);
    insert($pdo, 'pages', ['site_id' => $site, 'title' => 'Upgrade proof', 'slug' => 'upgrade-proof', 'status' => 'published', 'content' => '<p>BR-2B /media/br2b.txt</p>', 'content_json' => '{"blocks":[{"type":"paragraph","data":{"text":"BR-2B /media/br2b.txt"}}]}']);
    $page = id($pdo, 'pages', 'slug', 'upgrade-proof');
    insert($pdo, 'page_revisions', ['page_id' => $page, 'title' => 'Upgrade proof', 'slug' => 'upgrade-proof', 'status' => 'published', 'content' => '<p>BR-2B /media/br2b.txt</p>', 'content_json' => '{"blocks":[{"type":"paragraph","data":{"text":"BR-2B /media/br2b.txt"}}]}']);
    insert($pdo, 'menus', ['site_id' => $site, 'code' => 'br2b', 'label' => 'BR-2B Menu', 'status' => 'active']);
    $menu = id($pdo, 'menus', 'code', 'br2b');
    insert($pdo, 'menu_items', ['menu_id' => $menu, 'page_id' => $page, 'label' => 'Upgrade proof', 'target' => '_self', 'position' => 10, 'status' => 'active']);
}

function insert(PDO $pdo, string $table, array $values): void
{
    $columns = $pdo->query('PRAGMA table_info(' . $table . ')')->fetchAll(PDO::FETCH_ASSOC);
    if ($columns === []) {
        throw new RuntimeException('Required release table is missing: ' . $table);
    }
    $row = $values;
    foreach ($columns as $column) {
        $name = (string) $column['name'];
        if ($name === 'id' || array_key_exists($name, $row) || (int) $column['notnull'] === 0 || $column['dflt_value'] !== null) {
            continue;
        }
        $type = strtoupper((string) $column['type']);
        $row[$name] = str_contains($type, 'INT') ? 0 : (str_contains($name, '_at') ? '2026-09-09 00:00:00' : 'br2b');
    }
    $names = array_keys($row);
    $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $names) . ') VALUES (:' . implode(', :', $names) . ')';
    $pdo->prepare($sql)->execute($row);
}

function snapshot(string $database, string $mediaRoot): array
{
    $pdo = connection($database);
    $fixtures = [];
    foreach (['admin_roles','admin_users','admin_user_roles','sites','site_domains','media_assets','pages','page_revisions','menus','menu_items'] as $table) {
        $rows = $pdo->query('SELECT * FROM ' . $table . ' ORDER BY 1')->fetchAll(PDO::FETCH_ASSOC);
        $fixtures[$table] = hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR));
    }
    $fixtures['media_file'] = hash_file('sha256', $mediaRoot . '/storage/media/original/br2b.txt');
    $migrations = $pdo->query('SELECT * FROM migrations ORDER BY 1')->fetchAll(PDO::FETCH_ASSOC);
    return ['fixtures' => $fixtures, 'migrations' => $migrations];
}

function connection(string $database): PDO
{
    $pdo = new PDO('sqlite:' . $database);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    return $pdo;
}

function id(PDO $pdo, string $table, string $column, string $value): int
{
    $statement = $pdo->prepare('SELECT id FROM ' . $table . ' WHERE ' . $column . ' = :value');
    $statement->execute(['value' => $value]);
    $id = $statement->fetchColumn();
    if ($id === false) {
        throw new RuntimeException('Fixture ID could not be resolved for ' . $table);
    }
    return (int) $id;
}

function run(string $command, ?string $cwd = null): string
{
    $prefix = $cwd === null ? '' : 'cd ' . q($cwd) . ' && ';
    exec($prefix . $command . ' 2>&1', $output, $code);
    if ($code !== 0) {
        throw new RuntimeException("Command failed: {$command}\n" . implode("\n", $output));
    }
    return implode("\n", $output);
}
function q(string $value): string { return escapeshellarg($value); }
function removeWorktree(string $repository, string $path): void { if (is_dir($path)) { exec('git -C ' . q($repository) . ' worktree remove --force ' . q($path) . ' >/dev/null 2>&1'); } }
function removeTree(string $path): void { if ($path !== '' && is_dir($path)) { exec('rm -rf ' . q($path)); } }
