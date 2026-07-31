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

    public function __construct(private string $basePath, ?string $cachePath = null)
    {
        $this->cachePath = $cachePath ?? rtrim($basePath, '/\\') . '/var/cache/modules.php';
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
        $this->writeAtomically($this->renderCacheFile($modules));

        return $modules;
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
        if (!is_file($this->cachePath)) {
            return true;
        }

        $cleared = unlink($this->cachePath);
        if ($cleared && function_exists('opcache_invalidate')) {
            opcache_invalidate($this->cachePath, true);
        }

        return $cleared;
    }

    public function cachePath(): string
    {
        return $this->cachePath;
    }

    public function isCompiled(): bool
    {
        return is_file($this->cachePath);
    }

    private function ensureCacheDirectoryExists(): void
    {
        $directory = dirname($this->cachePath);

        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create module cache directory: ' . $directory);
        }
    }

    private function writeAtomically(string $contents): void
    {
        $directory = dirname($this->cachePath);
        $temporaryPath = tempnam($directory, '.modules-');
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
            ];
        }

        $generatedAt = gmdate('c');

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
 * Generated: {$generatedAt}
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

