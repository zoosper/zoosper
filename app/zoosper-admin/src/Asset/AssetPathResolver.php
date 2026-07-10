<?php

declare(strict_types=1);

namespace Zoosper\Admin\Asset;

use Zoosper\Core\Config\ConfigRepository;

/**
 * Resolves canonical public asset paths for admin/frontend/module assets.
 *
 * Static asset paths must never contain runtime-sensitive values such as OTPs,
 * TOTP secrets, recovery codes, payment data, session IDs or customer private
 * data. This resolver only works with static configured path prefixes.
 */
final readonly class AssetPathResolver
{
    public function __construct(private ConfigRepository $config)
    {
    }

    /**
     * Build a path under the configured admin asset namespace.
     */
    public function admin(string $path): string
    {
        return $this->join($this->configValue('assets.admin_path', '/assets/admin'), $path);
    }

    /**
     * Build a path under the configured frontend asset namespace.
     */
    public function frontend(string $path): string
    {
        return $this->join($this->configValue('assets.frontend_path', '/assets/frontend'), $path);
    }

    /**
     * Build a path under the configured module asset namespace.
     */
    public function module(string $moduleName, string $path): string
    {
        return $this->join($this->join($this->configValue('assets.module_path', '/assets/modules'), $moduleName), $path);
    }

    /**
     * Return a configured string value with a safe fallback.
     */
    private function configValue(string $key, string $default): string
    {
        return rtrim((string) ($this->config->get($key, $default) ?? $default), '/');
    }

    /**
     * Join URL path fragments while preserving a leading slash.
     */
    private function join(string $base, string $path): string
    {
        return '/' . trim($base, '/') . '/' . ltrim($path, '/');
    }
}
