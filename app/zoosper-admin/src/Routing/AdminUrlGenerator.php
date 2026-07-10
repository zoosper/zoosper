<?php

declare(strict_types=1);

namespace Zoosper\Admin\Routing;

/**
 * Generates admin URLs using the configured admin front name.
 *
 * Admin templates and controllers should use this service instead of hard-coded
 * `/admin` strings once Phase 0.24 is wired into the current controllers and
 * route loaders.
 */
final readonly class AdminUrlGenerator
{
    public function __construct(private AdminPathResolver $paths)
    {
    }

    /**
     * Build an admin URL for a relative admin route.
     *
     * @param array<string, string|int|bool|null> $query Optional query params.
     */
    public function to(string $path = '', array $query = []): string
    {
        $path = trim($path, '/');
        $url = $this->paths->basePath() . ($path !== '' ? '/' . $path : '');
        $filteredQuery = array_filter($query, static fn (mixed $value): bool => $value !== null && $value !== '');

        return $filteredQuery !== [] ? $url . '?' . http_build_query($filteredQuery) : $url;
    }
}
