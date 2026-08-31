<?php

declare(strict_types=1);

namespace Zoosper\Database;

use Closure;
use PDO;

/**
 * Resolves and memoizes the application's PDO connection on first use.
 */
final class PdoConnectionProvider
{
    private ?PDO $connection = null;

    /** @param Closure(): PDO $factory */
    public function __construct(private readonly Closure $factory)
    {
    }

    public function get(): PDO
    {
        return $this->connection ??= ($this->factory)();
    }
}











