<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\BulkAction;

use InvalidArgumentException;

/** Callable adapter for the host application's existing CSRF validator. */
final readonly class GridBulkCsrfVerifier implements GridBulkCsrfVerifierInterface
{
    /** @param callable(string): bool $validator */
    public function __construct(private mixed $validator)
    {
        if (!is_callable($validator)) {
            throw new InvalidArgumentException('Grid bulk CSRF validator must be callable.');
        }
    }

    public function assertValid(string $token): void
    {
        if ($token === '' || !($this->validator)($token)) {
            throw new InvalidArgumentException('Invalid Grid bulk-action CSRF token.');
        }
    }
}
