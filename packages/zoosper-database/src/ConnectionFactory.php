<?php

declare(strict_types=1);

namespace Zoosper\Database;

use PDO;
use RuntimeException;
use Zoosper\Core\Config\ConfigRepository;

/**
 * Creates PDO database connections for SQLite or MySQL/MariaDB with prepared statement enforcement.
 */
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

        $driver = (string) ($connection['driver'] ?? $default);
        $environment = (string) $this->config->get('app.env', 'local');
        $policy = $this->config->array('database_policy');
        $enforceMysql = (bool) ($policy['enforce_mysql_in_production'] ?? true);

        if (in_array(strtolower($environment), ['staging', 'production'], true) && $enforceMysql && $driver === 'sqlite') {
            throw new RuntimeException(
                'SQLite database driver is not permitted in ' . $environment . ' environments. '
                . 'Production environments require a MySQL/MariaDB connection.'
            );
        }

        if ($driver === 'mysql') {
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
        if ($database === ':memory:') {
            $path = ':memory:';
        } elseif (str_starts_with($database, '/') || str_starts_with($database, '\\') || (strlen($database) > 1 && $database[1] === ':')) {
            $path = $database;
        } else {
            $path = $this->basePath . '/' . $database;
        }

        if ($path !== ':memory:' && !is_dir(dirname($path))) {
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

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // SECURITY FIX: use real, server-side prepared statements instead
            // of PHP's client-side emulated binding.
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO($dsn, (string) $connection['username'], (string) $connection['password'], $options);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $pdo;
    }
}











