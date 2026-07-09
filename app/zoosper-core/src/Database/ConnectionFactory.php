<?php

declare(strict_types=1);

namespace Zoosper\Core\Database;

use PDO;
use RuntimeException;
use Zoosper\Core\Config\ConfigRepository;

final readonly class ConnectionFactory
{
    public function __construct(
        private ConfigRepository $config,
        private string $basePath,
    ) {
    }

    public function create(): PDO
    {
        $default = (string) $this->config->get('database.default', 'sqlite');
        $connection = $this->config->get('database.connections.' . $default);

        if (!is_array($connection)) {
            throw new RuntimeException('Database connection is not configured: ' . $default);
        }

        if (($connection['driver'] ?? $default) === 'mysql') {
            return $this->createMysqlConnection($connection);
        }

        return $this->createSqliteConnection($connection);
    }

    /**
     * @param array<string, mixed> $connection
     */
    private function createSqliteConnection(array $connection): PDO
    {
        $database = (string) ($connection['database'] ?? 'storage/database/zoosper.sqlite');
        $path = str_starts_with($database, '/')
            ? $database
            : $this->basePath . '/' . $database;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        $pdo = new PDO('sqlite:' . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');

        return $pdo;
    }

    /**
     * @param array<string, mixed> $connection
     */
    private function createMysqlConnection(array $connection): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            (string) $connection['host'],
            (int) $connection['port'],
            (string) $connection['database'],
            (string) ($connection['charset'] ?? 'utf8mb4'),
        );

        $pdo = new PDO($dsn, (string) $connection['username'], (string) $connection['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }
}
