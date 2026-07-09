<?php

declare(strict_types=1);

namespace Zoosper\Core\Schema;

final readonly class SchemaValidationResult
{
    /** @param list<string> $errors */
    public function __construct(public array $errors)
    {
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }
}
