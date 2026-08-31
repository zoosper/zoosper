<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Transport;

use InvalidArgumentException;

final readonly class ApiRequest
{
    /**
     * @param array<string, scalar|list<scalar>|null> $query
     * @param array<string, string> $headers
     */
    public function __construct(
        public string $method,
        public string $endpoint,
        public array $query = [],
        public array $headers = [],
    ) {
        if (!in_array($method, ['GET', 'HEAD'], true)) {
            throw new InvalidArgumentException('API Grid transport supports read-only GET and HEAD requests.');
        }
        if ($endpoint === '' || !str_starts_with($endpoint, '/')) {
            throw new InvalidArgumentException('API Grid endpoint must be a non-empty absolute path.');
        }
    }

    /** @param array<string, string> $headers */
    public function withHeaders(array $headers): self
    {
        return new self($this->method, $this->endpoint, $this->query, [...$this->headers, ...$headers]);
    }
}











