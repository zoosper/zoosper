<?php

declare(strict_types=1);

namespace Zoosper\Admin\Routing;

use Zoosper\Core\Config\ConfigRepository;

/**
 * Resolves the configured admin front name.
 *
 * The admin path is configurable so deployments can avoid a predictable
 * hard-coded `/admin` URL. This is a noise-reduction feature, not a replacement
 * for real security controls such as ACL, CSRF, secure sessions and 2FA.
 */
final readonly class AdminPathResolver
{
    public function __construct(private ConfigRepository $config)
    {
    }

    /**
     * Return the configured admin path without leading or trailing slashes.
     */
    public function path(): string
    {
        $path = trim((string) ($this->config->get('admin.path', 'admin') ?? 'admin'), '/');

        return $path !== '' ? $path : 'admin';
    }

    /**
     * Return the configured admin base path with a leading slash.
     */
    public function basePath(): string
    {
        return '/' . $this->path();
    }

    /**
     * Determine whether a request path belongs to the configured admin area.
     */
    public function matches(string $requestPath): bool
    {
        $requestPath = '/' . trim($requestPath, '/');
        $basePath = $this->basePath();

        return $requestPath === $basePath || str_starts_with($requestPath, $basePath . '/');
    }
}
