<?php

declare(strict_types=1);

namespace Zoosper\Core\Url;

use InvalidArgumentException;
use Zoosper\Core\Config\ConfigRepository;

/**
 * Canonical, configuration-backed generator for internal admin URLs.
 *
 * Route declarations may continue to use the canonical /admin prefix while
 * the route loader migration is completed. Runtime producers should use this
 * service instead of embedding /admin literals.
 */
final readonly class AdminUrlGenerator
{
    private string $basePath;

    public function __construct(ConfigRepository $config)
    {
        $admin = $config->array('admin');
        $this->basePath = self::normalise((string) ($admin['base_path'] ?? '/admin'));
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    /**
     * Build an admin URL and optionally append an encoded query string.
     *
     * @param array<string, scalar|null> $query
     */
    public function url(string $path = '', array $query = []): string
    {
        $relative = ltrim($path, '/');
        $url = $relative === '' ? $this->basePath : $this->basePath . '/' . $relative;
        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        return $queryString === '' ? $url : $url . '?' . $queryString;
    }

    public function isAdminPath(string $path): bool
    {
        $requestPath = parse_url($path, PHP_URL_PATH);
        if (!is_string($requestPath)) {
            return false;
        }

        return $requestPath === $this->basePath
            || str_starts_with($requestPath, $this->basePath . '/');
    }

    /** Replace only the canonical leading /admin segment in a route or menu path. */
    public function expandCanonicalPath(string $path): string
    {
        if ($path === '/admin') {
            return $this->basePath;
        }

        if (str_starts_with($path, '/admin/')) {
            return $this->basePath . substr($path, strlen('/admin'));
        }

        return $path;
    }

    private static function normalise(string $path): string
    {
        $path = '/' . trim($path, '/');
        if ($path === '/') {
            throw new InvalidArgumentException('Admin base path cannot be the site root.');
        }

        $reserved = ['/api', '/asset', '/assets', '/static'];
        foreach ($reserved as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                throw new InvalidArgumentException('Admin base path conflicts with reserved prefix: ' . $prefix);
            }
        }

        if (preg_match('#^/[A-Za-z0-9][A-Za-z0-9/_-]*$#', $path) !== 1) {
            throw new InvalidArgumentException('Admin base path contains unsupported characters.');
        }

        return $path;
    }
}
