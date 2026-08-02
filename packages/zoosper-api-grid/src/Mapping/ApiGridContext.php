<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Mapping;

use InvalidArgumentException;

final readonly class ApiGridContext
{
    /** @param array<string, scalar|null> $scope */
    public function __construct(
        public int $adminUserId,
        public ?int $siteId = null,
        public array $scope = [],
    ) {
    }

    public function requireInt(string $key): int
    {
        $value = $this->scope[$key] ?? null;
        if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
            throw new InvalidArgumentException("API Grid context value '{$key}' must be an integer.");
        }

        return (int) $value;
    }
}
