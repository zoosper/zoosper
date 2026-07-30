<?php

declare(strict_types=1);

namespace Zoosper\Core\Database;

use PDO;
use RuntimeException;
use Zoosper\Core\Config\ConfigRepository;

/**
 * SECURITY/CORRECTNESS FIX (confirmed 2026-07-30, external reviewer pass):
 * createMysqlConnection() previously never set PDO::ATTR_EMULATE_PREPARES
 * explicitly, meaning it used PHP's own default for the mysql PDO driver —
 * which is `true` (client-side "emulated" prepares). Under emulation, bound
 * values are interpolated into the SQL string by the PHP driver itself
 * BEFORE it reaches MySQL/MariaDB, rather than being sent separately over
 * the wire as real, typed protocol parameters and bound server-side. This
 * is weaker in two concrete ways: (1) type safety is looser — e.g. an int
 * bound where the server expects a genuine integer protocol value is
 * instead coerced into a quoted string literal by the client library, and
 * (2) it narrows (though does not eliminate — parameterised queries were
 * already used throughout this codebase) the safety margin true
 * server-side prepared statements provide against injection.
 *
 * Fixed by explicitly setting PDO::ATTR_EMULATE_PREPARES => false so MySQL/
 * MariaDB connections use real, server-side prepared statements. This is a
 * one-line, well-known PDO hardening setting.
 *
 * SQLite is unaffected: SQLite has no client/server split in the same
 * sense, and PDO's own sqlite driver has never supported/needed this
 * attribute the way the mysql driver does — createSqliteConnection() is
 * deliberately left untouched.
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
        // SECURITY FIX: use real, server-side prepared statements instead
        // of PHP's client-side emulated binding (the previous, implicit
        // default for the mysql PDO driver).
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $pdo;
    }
}
