<?php

declare(strict_types=1);

function zoosper_phase077_load_env(string $basePath): void
{
    $file = $basePath . '/.env';
    if (!is_file($file)) {
        return;
    }

    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

function zoosper_phase077_pdo(string $basePath): PDO
{
    zoosper_phase077_load_env($basePath);

    if (class_exists(\Zoosper\Bootstrap\EnvLoader::class)) {
        // Let current app bootstrap stay in control when available.
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_DATABASE') ?: getenv('DB_NAME') ?: 'zoosper';
    $username = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: '';
    $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

    return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function zoosper_phase077_column_exists(PDO $pdo, string $table, string $column): bool
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column'
    );
    $statement->execute(['table' => $table, 'column' => $column]);

    return (int) $statement->fetchColumn() > 0;
}
