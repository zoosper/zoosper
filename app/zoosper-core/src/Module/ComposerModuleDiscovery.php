<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

/**
 * Discovers Composer-installed Zoosper marketplace modules from vendor metadata.
 *
 * Marketplace packages should identify themselves using Composer metadata:
 *
 * - composer.json type: "zoosper-module"
 * - optional extra.zoosper.module: "module.php"
 *
 * This class reads vendor/composer/installed.json and returns explicit module.php
 * files only. It deliberately avoids recursively scanning all vendor folders,
 * which keeps discovery predictable and avoids accidentally executing unrelated
 * package files.
 */
final readonly class ComposerModuleDiscovery
{
    public function __construct(private string $basePath)
    {
    }

    /**
     * Return Composer-installed module.php files for packages marked as Zoosper modules.
     *
     * @return list<string>
     */
    public function moduleFiles(): array
    {
        $installedJson = $this->basePath . '/vendor/composer/installed.json';
        if (!is_file($installedJson)) {
            return [];
        }

        $contents = file_get_contents($installedJson);
        if ($contents === false || trim($contents) === '') {
            return [];
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            return [];
        }

        $packages = $this->packagesFromInstalledJson($decoded);
        $moduleFiles = [];

        foreach ($packages as $package) {
            if (!$this->isZoosperModulePackage($package)) {
                continue;
            }

            $packageRoot = $this->packageRoot($package);
            if ($packageRoot === null) {
                continue;
            }

            $moduleRelativePath = $this->moduleRelativePath($package);
            $moduleFile = $packageRoot . '/' . ltrim($moduleRelativePath, '/');
            if (is_file($moduleFile)) {
                $moduleFiles[] = $moduleFile;
            }
        }

        $moduleFiles = array_values(array_unique($moduleFiles));
        sort($moduleFiles);

        return $moduleFiles;
    }

    /**
     * @param array<string, mixed> $installed
     * @return list<array<string, mixed>>
     */
    private function packagesFromInstalledJson(array $installed): array
    {
        if (isset($installed['packages']) && is_array($installed['packages'])) {
            return array_values(array_filter($installed['packages'], 'is_array'));
        }

        if (isset($installed[0]) && is_array($installed[0])) {
            return array_values(array_filter($installed, 'is_array'));
        }

        return [];
    }

    /**
     * @param array<string, mixed> $package
     */
    private function isZoosperModulePackage(array $package): bool
    {
        if (($package['type'] ?? null) === 'zoosper-module') {
            return true;
        }

        $extra = $package['extra'] ?? [];
        return is_array($extra)
            && isset($extra['zoosper'])
            && is_array($extra['zoosper'])
            && isset($extra['zoosper']['module']);
    }

    /**
     * @param array<string, mixed> $package
     */
    private function moduleRelativePath(array $package): string
    {
        $extra = $package['extra'] ?? [];
        if (is_array($extra) && isset($extra['zoosper']) && is_array($extra['zoosper'])) {
            $module = $extra['zoosper']['module'] ?? null;
            if (is_string($module) && trim($module) !== '') {
                return trim($module);
            }
        }

        return 'module.php';
    }

    /**
     * @param array<string, mixed> $package
     */
    private function packageRoot(array $package): ?string
    {
        $installPath = $package['install-path'] ?? null;
        if (is_string($installPath) && trim($installPath) !== '') {
            $path = $installPath;
            if (!str_starts_with($path, '/')) {
                $path = $this->basePath . '/vendor/composer/' . $path;
            }

            return realpath($path) ?: $this->normalisePath($path);
        }

        $name = $package['name'] ?? null;
        if (!is_string($name) || !str_contains($name, '/')) {
            return null;
        }

        return $this->basePath . '/vendor/' . $name;
    }

    private function normalisePath(string $path): string
    {
        $segments = [];
        foreach (explode('/', str_replace('\\', '/', $path)) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);
                continue;
            }

            $segments[] = $segment;
        }

        return '/' . implode('/', $segments);
    }
}
