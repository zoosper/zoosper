<?php

declare(strict_types=1);

namespace Zoosper\Core\Composer;

use RuntimeException;

/**
 * Synchronises Composer PSR-4 mappings from enabled Zoosper module metadata.
 *
 * Supports historical app modules, local package-path modules, community modules
 * and Vendor_Module/package-friendly identities. This keeps root development
 * autoloading intact while packages are gradually extracted.
 */
final readonly class ModuleAutoloadSynchronizer
{
    public function __construct(private string $basePath)
    {
    }

    /**
     * Update composer.json in-place and return the mappings that were ensured.
     *
     * @return array{autoload: array<string, string>, autoload-dev: array<string, string>, changed: bool}
     */
    public function sync(string $composerFile = 'composer.json'): array
    {
        $path = $this->absolutePath($composerFile);
        if (!is_file($path)) {
            throw new RuntimeException('composer.json not found: ' . $path);
        }

        $json = json_decode((string) file_get_contents($path), true);
        if (!is_array($json)) {
            throw new RuntimeException('composer.json could not be decoded as JSON.');
        }

        $mappings = $this->discoverMappings();
        $changed = $this->applyMappings($json, $mappings);

        if ($changed) {
            file_put_contents($path, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        }

        return $mappings + ['changed' => $changed];
    }

    /**
     * Discover PSR-4 mappings from enabled module folders.
     *
     * @return array{autoload: array<string, string>, autoload-dev: array<string, string>}
     */
    public function discoverMappings(): array
    {
        $autoload = [];
        $autoloadDev = [];

        foreach ($this->moduleFiles() as $moduleFile) {
            $module = require $moduleFile;
            if (!is_array($module) || ($module['enabled'] ?? true) === false) {
                continue;
            }

            $moduleDir = dirname($moduleFile);
            $identity = ModulePackageIdentity::fromModule($module, basename($moduleDir));
            if ($identity === null) {
                continue;
            }

            $srcDir = $moduleDir . '/src';
            if (is_dir($srcDir)) {
                $autoload[$identity->namespace] = $this->relativeDirectory($srcDir);
            }

            $testsDir = $moduleDir . '/tests';
            if (is_dir($testsDir)) {
                $autoloadDev[$identity->namespace . 'Tests\\'] = $this->relativeDirectory($testsDir);
            }
        }

        ksort($autoload);
        ksort($autoloadDev);

        return ['autoload' => $autoload, 'autoload-dev' => $autoloadDev];
    }

    /**
     * @param array<string, mixed> $composer
     * @param array{autoload: array<string, string>, autoload-dev: array<string, string>} $mappings
     */
    private function applyMappings(array &$composer, array $mappings): bool
    {
        $changed = false;
        foreach (['autoload', 'autoload-dev'] as $section) {
            $composer[$section] ??= [];
            if (!is_array($composer[$section])) {
                $composer[$section] = [];
                $changed = true;
            }
            $composer[$section]['psr-4'] ??= [];
            if (!is_array($composer[$section]['psr-4'])) {
                $composer[$section]['psr-4'] = [];
                $changed = true;
            }

            foreach ($mappings[$section] as $namespace => $path) {
                if (($composer[$section]['psr-4'][$namespace] ?? null) !== $path) {
                    $composer[$section]['psr-4'][$namespace] = $path;
                    $changed = true;
                }
            }

            ksort($composer[$section]['psr-4']);
        }

        return $changed;
    }

    /** @return list<string> */
    private function moduleFiles(): array
    {
        $patterns = [
            $this->basePath . '/app/*/module.php',
            $this->basePath . '/packages/*/module.php',
            $this->basePath . '/modules/*/module.php',
            $this->basePath . '/modules/*/*/module.php',
        ];

        $files = [];
        foreach ($patterns as $pattern) {
            foreach (glob($pattern) ?: [] as $file) {
                $files[] = $file;
            }
        }

        sort($files);

        return $files;
    }

    private function relativeDirectory(string $directory): string
    {
        $relative = ltrim(str_replace($this->basePath, '', $directory), '/\\');

        return rtrim(str_replace('\\', '/', $relative), '/') . '/';
    }

    private function absolutePath(string $path): string
    {
        return str_starts_with($path, '/') ? $path : rtrim($this->basePath, '/') . '/' . ltrim($path, '/');
    }
}
