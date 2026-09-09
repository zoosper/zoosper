<?php

declare(strict_types=1);

use Zoosper\Core\Testing\Upgrade\MySqlUpgradeDatabaseWorkspace;

require dirname(__DIR__) . '/vendor/autoload.php';

if (PHP_SAPI !== 'cli' || trim((string) shell_exec('id -un')) !== 'vagrant') {
    throw new RuntimeException('MySQL upgrade capability verification is CLI-only and must run as vagrant.');
}

foreach (['BR2D_MYSQL_HOST', 'BR2D_MYSQL_PORT', 'BR2D_MYSQL_USERNAME', 'BR2D_MYSQL_PASSWORD'] as $name) {
    if (getenv($name) === false || getenv($name) === '') {
        throw new RuntimeException($name . ' must be supplied explicitly for the disposable MySQL proof.');
    }
}

$host = (string) getenv('BR2D_MYSQL_HOST');
$port = (int) getenv('BR2D_MYSQL_PORT');
$username = (string) getenv('BR2D_MYSQL_USERNAME');
$password = (string) getenv('BR2D_MYSQL_PASSWORD');
$pdo = new PDO(
    sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port),
    $username,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
);

$workspace = new MySqlUpgradeDatabaseWorkspace($pdo);
$result = $workspace->run(static function (string $database) use ($pdo): array {
    $statement = $pdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :database');
    $statement->execute(['database' => $database]);
    if ($statement->fetchColumn() !== $database) {
        throw new RuntimeException('Disposable MySQL database was not visible after creation.');
    }
    return ['driver' => 'mysql', 'database_created' => true];
});
if (!$workspace->cleanupCompleted()) {
    throw new RuntimeException('Disposable MySQL database still exists after cleanup.');
}
$result['database_dropped'] = true;

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), PHP_EOL;
