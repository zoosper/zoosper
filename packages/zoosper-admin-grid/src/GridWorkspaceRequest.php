<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/**
 * Transport-neutral request DTO for feature-owned Grid workspace controllers.
 * Authentication identity and grid identity are deliberately absent.
 */
final readonly class GridWorkspaceRequest
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $post
     */
    public function __construct(
        public string $method,
        public array $query = [],
        public array $post = [],
    ) {
    }

    public function isMutation(): bool
    {
        return strtoupper($this->method) === 'POST';
    }

    public function action(): string
    {
        return trim((string) ($this->post['action'] ?? ''));
    }
}











