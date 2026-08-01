<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

/** Immutable, parameterised WHERE and allow-listed ORDER BY fragments. */
final readonly class AuthGridSqlPlan
{
    /** @param array<string, string|int> $parameters */
    public function __construct(
        public string $whereSql,
        public string $orderSql,
        public array $parameters,
    ) {
    }
}
