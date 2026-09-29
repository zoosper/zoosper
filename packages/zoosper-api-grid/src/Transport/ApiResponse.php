<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Transport;

use InvalidArgumentException;

final readonly class ApiResponse
{
    /**
     * @param array<string, mixed>|list<mixed> $decodedBody
     * @param array<string, string> $headers
     */
    public function __construct(
        public int $statusCode,
        public array $decodedBody,
        public int $receivedBodyBytes,
        public array $headers = [],
    ) {
        if ($receivedBodyBytes < 0) {
            throw new InvalidArgumentException('API Grid received-body byte count must not be negative.');
        }
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }
}











