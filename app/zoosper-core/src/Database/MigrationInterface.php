<?php

declare(strict_types=1);

namespace Zoosper\Core\Database;

use PDO;

interface MigrationInterface
{
    public function name(): string;

    public function up(PDO $pdo, string $driver): void;
}
