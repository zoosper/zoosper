<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

use RuntimeException;

/**
 * Compiles the live module list into a cached PHP file
 * (var/cache/modules.php by default), so that ModuleRegistry::enabledModules()
 * can skip the filesystem glob-scan entirely on every subsequent request.
 *
 * This is the foundational piece of the broader "no compiled/cached module
 * discovery" fix both external reviewer passes flagged (~15 independently
 * hand-rolled discovery loops — routes, services, controllers, events, ACL,
 * translations, grid columns, etc. — all re-scanning the filesystem on every
 * request, none cached). Every one of those 15 mechanisms already calls
 * ModuleRegistry::enabledModules() as its first step, so caching THAT list is
 * the single highest-leverage starting point: it doesn't fix all 15 loops by
 * itself, but every one of them immediately benefits from not having to wait
 * on a fresh filesystem scan just to get the module list they then iterate.
 * Future phases can apply this same "compile once, cache to disk, fall back
 * to live if missing/corrupt" pattern to routes/services/etc. individually.
 *
 * Deliberately opt-in and disk-cache-based (not APCu/Redis/etc.) to keep the
 * fix simple, dependency-free, and trivially inspectable (the cache is just
 * a plain PHP file you can open and read).
 */
final readonly class ModuleManifestCompiler
{
    private string $cachePath;
    private string $cacheDir;

    public function __construct(private string $basePath, ?string $cachePath = null)
    {
        $this->cachePath = $cachePath ?? rtrim($basePath, '/\\') . '/var/cache/modules.php';
        $this->cacheDir = dirname($this->cachePath);
    }

    /**
     * Perform a guaranteed-fresh live filesystem scan (via a NEW ModuleRegistry
     * instance's discoverModulesLive() — never a cached read, even if a
     * compiled cache already exists from a previous run) and write the result
     * to the compiled cache file.
     *
     * @return list<Module> the modules that were compiled, for CLI reporting
     */
    public function compile(): array
    {
        $registry = new ModuleRegistry($this->basePath);
        $modules = $registry->discoverModulesLive();

        $this->ensureCacheDirectoryExists();
        $this->writeManifest($this->renderCacheFile($modules));

        $this->compileServices($modules);
        $this->compileRoutes($modules, 'admin_routes.php', 'routes_admin_compiled.php');
        $this->compileRoutes($modules, 'api_routes.php', 'routes_api_compiled.php');

        return $modules;
    }

    private function compileServices(array $modules): void
    {
        $services = [];
        $decorators = [];

        foreach ($modules as $module) {
            if ($module->discovery['services'] ?? false) {
                $services[] = $module->configPath('services.php');
            }
            if ($module->discovery['service_decorators'] ?? false) {
                $decorators[] = $module->configPath('service_decorators.php');
            }
        }

        $this->writeAtomically(
            $this->cacheDir . '/services_compiled.php',
            $this->renderAggregatedFile($services, $decorators)
        );
    }

    private function compileRoutes(array $modules, string $configFileName, string $cacheFileName): void
    {
        $discoveryKey = $configFileName === 'admin_routes.php' ? 'routes_admin' : 'routes_api';
        $files = [];

        foreach ($modules as $module) {
            if ($module->discovery[$discoveryKey] ?? false) {
                $files[] = $module->configPath($configFileName);
            }
        }

        $this->writeAtomically(
            $this->cacheDir . '/' . $cacheFileName,
            $this->renderAggregatedFile($files)
        );
    }

    private function renderAggregatedFile(array $files, array $decorators = []): string
    {
        $generatedAt = gmdate('c');
        $content = "<?php\n\ndeclare(strict_types=1);\n\n/**\n * COMPILED AGGREGATED CONFIG — Generated: {$generatedAt}\n */\n\n";

        if (empty($decorators)) {
            $content .= "return array_merge(\n";
            foreach ($files as $file) {
                $content .= "    require " . var_export($file, true) . ",\n";
            }
            $content .= ");\n";
        } else {
            // Special case for services + decorators
            $content .= "\$definitions = array_merge(\n";
            foreach ($files as $file) {
                $content .= "    require " . var_export($file, true) . ",\n";
            }
            $content .= ");\n\n";
            $content .= "\$decorators = array_merge(\n";
            foreach ($decorators as $file) {
                $content .= "    require " . var_export($file, true) . ",\n";
            }
            $content .= ");\n\n";
            $content .= "return ['definitions' => \$definitions, 'decorators' => \$decorators];\n";
        }

        return $content;
    }

    /**
     * Delete the compiled cache file, forcing the next ModuleRegistry
     * instance to fall back to a live filesystem scan.
     *
     * @return bool true if a cache file existed and was removed, or if none
     *              existed to begin with (both are a "successfully cleared"
     *              outcome from the caller's point of view)
     */
    public function clear(): bool
    {
        $files = [
            $this->cachePath(),
            $this->cacheDir . '/services_compiled.php',
            $this->cacheDir . '/routes_admin_compiled.php',
            $this->cacheDir . '/routes_api_compiled.php',
        ];

        $allCleared = true;
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            if (!unlink($file)) {
                $allCleared = false;
                continue;
            }

            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($file, true);
            }
        }

        return $allCleared;
    }

    public function cachePath(): string
    {
        return $this->cachePath;
    }

    public function isCompiled(): bool
    {
        return is_file($this->cachePath);
    }

    private function writeManifest(string $contents): void
    {
        $directory = dirname($this->cachePath);
        $temporaryPath = tempnam($directory, '.' . basename($this->cachePath) . '-');
        if ($temporaryPath === false) {
            throw new RuntimeException(
                'Unable to create temporary module cache file in: ' . $directory,
            );
        }

        try {
            $bytes = file_put_contents($temporaryPath, $contents, LOCK_EX);
            if ($bytes === false || $bytes !== strlen($contents)) {
                throw new RuntimeException(
                    'Unable to write complete module cache file: ' . $temporaryPath,
                );
            }

            if (!rename($temporaryPath, $this->cachePath)) {
                throw new RuntimeException(
                    'Unable to atomically replace module cache file: ' . $this->cachePath,
                );
            }

            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($this->cachePath, true);
            }
        } finally {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    private function ensureCacheDirectoryExists(): void
    {
        if (is_dir($this->cacheDir)) {
            return;
        }

        if (!mkdir($this->cacheDir, 0775, true) && !is_dir($this->cacheDir)) {
            throw new RuntimeException('Unable to create module cache directory: ' . $this->cacheDir);
        }
    }

    private function writeAtomically(string $path, string $contents): void
    {
        $directory = dirname($path);
        $temporaryPath = tempnam($directory, '.' . basename($path) . '-');
        if ($temporaryPath === false) {
            throw new RuntimeException(
                'Unable to create temporary module cache file in: ' . $directory,
            );
        }

        try {
            $bytes = file_put_contents($temporaryPath, $contents, LOCK_EX);
            if ($bytes === false || $bytes !== strlen($contents)) {
                throw new RuntimeException(
                    'Unable to write complete module cache file: ' . $temporaryPath,
                );
            }

            if (!rename($temporaryPath, $path)) {
                throw new RuntimeException(
                    'Unable to atomically replace module cache file: ' . $path,
                );
            }

            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($path, true);
            }
        } finally {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    /**
     * @param list<Module> $modules
     */
    private function renderCacheFile(array $modules): string
    {
        $export = [];
        foreach ($modules as $module) {
            $export[] = [
                'name' => $module->name,
                'path' => $module->path,
                'enabled' => $module->enabled,
                'version' => $module->version,
                'sortOrder' => $module->sortOrder,
                'source' => $module->source,
                'discovery' => $module->discovery,
            ];
        }

        $generatedAt = gmdate('c');
        $stamps = (new ModuleManifestFreshness($this->basePath))->stamps();
        $composerLockHash = $stamps['composerLock'];
        $firstPartyModulesHash = $stamps['firstPartyModules'];

        return <<<PHP
<?php

declare(strict_types=1);

/**
 * COMPILED MODULE MANIFEST — generated by `bin/zoosper compile`.
 *
 * Do NOT edit this file by hand. It is regenerated entirely from a live
 * filesystem scan every time `bin/zoosper compile` runs. If a module was
 * added, removed, enabled, or disabled and you don't see the change take
 * effect, run `bin/zoosper compile` again (or `bin/zoosper cache:clear` to
 * delete this file and force live discovery until you next compile).
 *
 * Composer-Lock-SHA256: {$composerLockHash}
 * First-Party-Modules-SHA256: {$firstPartyModulesHash}
 */

return {$this->exportArray($export)};

PHP;
    }

    /** @param list<array<string, mixed>> $export */
    private function exportArray(array $export): string
    {
        return var_export($export, true);
    }
}

