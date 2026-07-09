<?php

declare(strict_types=1);

namespace Zoosper\Core\Security;

final readonly class SecurityHeaders
{
    /** @param array<string, string> $headers */
    public function __construct(private array $headers)
    {
    }

    public function apply(): void
    {
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
    }
}
