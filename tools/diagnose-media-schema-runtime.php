<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper media runtime schema diagnostics\n";
print "========================================\n\n";

$connection = (string) env('DB_CONNECTION', 'mysql');
$pdo = null;

try {
    if ($connection === 'sqlite') {
        $database = (string) env('DB_DATABASE', $basePath . '/storage/database/zoosper.sqlite');
        $database = str_starts_with($database, '/') ? $database : $basePath . '/' . $database;
        $pdo = new PDO('sqlite:' . $database);
    } else {
        $host = (string) env('DB_HOST', '127.0.0.1');
        $port = (string) env('DB_PORT', '3306');
        $database = (string) env('DB_DATABASE', 'zoosper');
        $username = (string) env('DB_USERNAME', 'root');
        $password = (string) env('DB_PASSWORD', '');
        $charset = (string) env('DB_CHARSET', 'utf8mb4');
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $database, $charset);
        $pdo = new PDO($dsn, $username, $password);
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->query('SELECT 1 FROM media_assets LIMIT 1');

    print "- database connection: ok\n";
    print "- media_assets table: ok\n";
    print "\nResult: OK\n";
    exit(0);
} catch (PDOException $exception) {
    print "- database connection/table check: FAIL\n";
    print "- error: " . $exception->getMessage() . "\n\n";
    print "Suggested fix:\n";
    print "  PHP=php8.5 bin/zoosper migrate\n";
    print "  php8.5 tools/diagnose-media-schema-runtime.php\n";
    print "  PHP=php8.5 bin/verify\n";
    print "\nResult: FAIL\n";
    exit(2);
}
