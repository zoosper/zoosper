<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Mapping;

interface ApiGridRowMapperInterface
{
    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>|object
     */
    public function map(array $record): array|object;
}
