<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Transport;

final readonly class ApiResponse
{
    /**
     * @param array<string, mixed>|list<mixed> $decodedBody
     * @param array<string, string> $headers
     */
    public function __construct(
        public int $statusCode,
        public array $decodedBody,
        public array $headers = [],
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }
}











