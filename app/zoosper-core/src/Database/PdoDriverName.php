<?php

declare(strict_types=1);

namespace Zoosper\Core\Database;

use PDO;
use RuntimeException;

/**
 * Reads the active PDO driver through one typed, fail-closed boundary.
 */
final class PdoDriverName
{
    public static function from(PDO $pdo): string
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if (!is_string($driver) || trim($driver) === '') {
            throw new RuntimeException('PDO did not return a non-empty string driver name.');
        }

        return strtolower(trim($driver));
    }
}
