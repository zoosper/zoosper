<?php

declare(strict_types=1);

namespace Zoosper\Core\Asset;

/**
 * Registry mapping a logical module name to the absolute base directory that
 * holds that module's public assets.
 *
 * Registration is plain data: a module declares its assets directory (typically
 * in its own config), and it is added here. Because a dropped-in module supplies
 * its own entry, no core edit is required to onboard a new module's assets.
 */
final class AssetModuleRegistry
{
    /** @var array<string, string> */
    private array $modules = [];

    /**
     * @param array<string, string> $modules logical name => absolute assets dir
     */
    public function __construct(array $modules = [])
    {
        foreach ($modules as $name => $dir) {
            $this->register((string) $name, (string) $dir);
        }
    }

    /**
     * Register (or override) a module's assets base directory.
     */
    public function register(string $module, string $assetsDir): void
    {
        if (preg_match('/^[A-Za-z0-9_.-]+$/', $module) !== 1) {
            throw new \InvalidArgumentException("Invalid asset module name: {$module}");
        }

        $real = realpath($assetsDir);
        // Allow registration even if the dir does not exist yet in some setups,
        // but prefer the canonical path when available.
        $this->modules[$module] = $real !== false ? $real : rtrim($assetsDir, '/');
    }

    public function has(string $module): bool
    {
        return isset($this->modules[$module]);
    }

    /**
     * Absolute assets base directory for a module, or null when unknown.
     */
    public function baseDir(string $module): ?string
    {
        return $this->modules[$module] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->modules;
    }
}
