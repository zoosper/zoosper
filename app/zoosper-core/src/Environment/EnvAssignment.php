<?php

declare(strict_types=1);

namespace Zoosper\Core\Environment;

final readonly class EnvAssignment
{
    public function __construct(
        public string $key,
        public string $value,
        public string $prefix,
        public string $suffix,
    ) {
    }

    public function withValue(string $value): string
    {
        return $this->prefix . $value . $this->suffix;
    }
}
