<?php

declare(strict_types=1);

namespace Zoosper\Core\Testing\Upgrade;

use PDO;
use RuntimeException;
use Throwable;

/** Owns one randomly named disposable MySQL database for an upgrade rehearsal. */
final class MySqlUpgradeDatabaseWorkspace
{
    private ?string $database = null;
    private ?string $lastDroppedDatabase = null;

    public function __construct(private readonly PDO $administrativeConnection)
    {
        if ($administrativeConnection->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
            throw new RuntimeException('BR-2D requires a MySQL administrative connection.');
        }
    }

    public function create(): string
    {
        if ($this->database !== null) {
            throw new RuntimeException('The disposable MySQL database has already been created.');
        }

        $database = 'zoosper_br2d_' . bin2hex(random_bytes(12));
        $this->administrativeConnection->exec(
            'CREATE DATABASE `' . $database . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
        );
        $this->database = $database;

        return $database;
    }

    public function drop(): void
    {
        if ($this->database === null) {
            return;
        }

        $database = $this->database;
        $this->database = null;
        $this->administrativeConnection->exec('DROP DATABASE IF EXISTS `' . $database . '`');
        $this->lastDroppedDatabase = $database;
    }

    public function cleanupCompleted(): bool
    {
        if ($this->lastDroppedDatabase === null) {
            return false;
        }

        $statement = $this->administrativeConnection->prepare(
            'SELECT 1 FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :database',
        );
        $statement->execute(['database' => $this->lastDroppedDatabase]);

        return $statement->fetchColumn() === false;
    }

    public function run(callable $proof): mixed
    {
        $database = $this->create();
        try {
            return $proof($database);
        } finally {
            $this->drop();
        }
    }

    public function __destruct()
    {
        try {
            $this->drop();
        } catch (Throwable) {
        }
    }
}
