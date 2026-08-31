<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Transport;

use InvalidArgumentException;

final readonly class ApiReliabilityPolicy
{
    public function __construct(
        public int $connectTimeoutMilliseconds = 1_000,
        public int $requestTimeoutMilliseconds = 5_000,
        public int $maximumResponseBytes = 2_000_000,
        public int $maximumSafeGetRetries = 0,
    ) {
        if ($connectTimeoutMilliseconds < 1 || $requestTimeoutMilliseconds < 1) {
            throw new InvalidArgumentException('API Grid timeouts must be positive.');
        }
        if ($maximumResponseBytes < 1 || $maximumSafeGetRetries < 0) {
            throw new InvalidArgumentException('API Grid response limit and retry count are invalid.');
        }
    }
}











