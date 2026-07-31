<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

use Throwable;
use Zoosper\Core\Composer\ModulePackageIdentity;

/**
 * Discovers enabled Zoosper modules from app/, local packages/ and Composer vendor/.
 *
 * Module discovery still supports the historical app/* layout, but Phase 1.37g
 * adds Composer-package discovery so modules can gradually move to path
 * repositories and later separate repositories without changing every consumer.
 *
 * Phase 1.108: enabledModules() is now memoized per instance (Sonnet Phase 2
 * §2.2/§3). Discovery performs multiple filesystem globs plus a `require` per
 * candidate module.php and a `json_decode` per vendor composer.json. Because a
 * SINGLE ModuleRegistry instance is constructed once in ApplicationFactory and
 * shared across every module-driven loader (services, controllers, routes,
 * events, entity-save listeners, admin menu, admin assets, translations), one
 * memoized call here removes the redundant re-scan from ALL of them for the
 * lifetime of the request/process. Discovery is pure (no side effects), so
 * caching is safe.
 *
 * Phase — compiled module manifest (foundation for the broader "no compiled
 * module discovery" fix both external reviewer passes flagged): the previous
 * memoization above only helps WITHIN a single request/process — a fresh
 * PHP-FPM request still re-globs the filesystem from scratch every time.
 * enabledModules() now checks for an optional, explicitly-generated compiled
 * cache file (var/cache/modules.php, produced by `bin/zoosper compile` via
 * ModuleManifestCompiler) BEFORE falling back to the live filesystem scan.
 *
 * This is fully backward compatible: if `bin/zoosper compile` has never been
 * run (the cache file doesn't exist), behaviour is IDENTICAL to before — a
 * live scan every time a fresh instance is constructed. Compiling is opt-in.
 * If the cache file exists but is corrupt/unreadable, discovery fails safe
 * back to a live scan rather than fataling the whole application.
 *
 * discoverModulesLive() is now a public method (previously the scan logic
 * lived inline in enabledModules()) specifically so ModuleManifestCompiler
 * can request a guaranteed-fresh scan when generating the cache — it must
 * never accidentally read its own previous cache output while compiling.
 */
final class ModuleRegistry
{
    /** @var list<Module>|null Cached result of the first enabledModules() call. */
    private ?array $cachedModules = null;

    private readonly string $compiledCachePath;

    public function __construct(private readonly string $basePath, ?string $compiledCachePath = null)
    {
        $this->compiledCachePath = $compiledCachePath ?? rtrim($basePath, '/\\') . '/var/cache/modules.php';
    }

    /** @return list<Module> */
    public function enabledModules(): array
    {
        if ($this->cachedModules !== null) {
            return $this->cachedModules;
        }

        $fromCompiledCache = $this->loadFromCompiledCache();
        if ($fromCompiledCache !== null) {
            $this->cachedModules = $fromCompiledCache;

            return $this->cachedModules;
        }

        $this->cachedModules = $this->discoverModulesLive();

        return $this->cachedModules;
    }

    /**
     * Force the next enabledModules() call to re-scan the filesystem. Not used
     * in normal request handling (module state cannot change mid-request); it
     * exists for long-lived worker processes (Swoole/FrankenPHP) or tests that
     * need to observe a changed module set within the same registry instance.
     *
     * Note: this only clears the per-instance memoization above — it does NOT
     * delete the compiled disk cache. Use ModuleManifestCompiler::clear() (or
     * `bin/zoosper cache:clear`) for that.
     */
    public function clearCache(): void
    {
        $this->cachedModules = null;
    }

    /**
     * Always performs a fresh filesystem scan, bypassing both the per-instance
     * memoization above and any compiled disk cache. Public specifically so
     * ModuleManifestCompiler can guarantee it is compiling live, current
     * truth — never its own stale prior output.
     *
     * @return list<Module>
     */
    public function discoverModulesLive(): array
    {
        /** @var array<string, Module> $modulesByName */
        $modulesByName = [];
        $seenRealPaths = [];

        foreach ($this->moduleCandidates() as $candidate) {
            $module = $this->moduleFromCandidate($candidate['moduleFile'], $candidate['source']);
            if ($module === null || !$module->enabled) {
                continue;
            }

            $realPath = realpath($module->path) ?: $module->path;
            if (isset($seenRealPaths[$realPath])) {
                continue;
            }
            $seenRealPaths[$realPath] = true;

            $identity = strtolower($module->name);
            $existing = $modulesByName[$identity] ?? null;
            if ($existing === null) {
                $modulesByName[$identity] = $module;
                continue;
            }

            $existingPriority = self::sourcePriority($existing->source);
            $candidatePriority = self::sourcePriority($module->source);

            if ($existingPriority === $candidatePriority) {
                throw DuplicateModuleException::sameLayer($existing, $module);
            }

            if ($candidatePriority > $existingPriority) {
                $modulesByName[$identity] = $module;
            }
        }

        $modules = array_values($modulesByName);
        usort($modules, static function (Module $a, Module $b): int {
            return [$a->sortOrder, $a->name] <=> [$b->sortOrder, $b->name];
        });

        return $modules;
    }

    /**
     * Attempt to load the module list from the compiled cache file. Returns
     * null (never throws) if the file doesn't exist, is unreadable, contains
     * unexpected data, or fails to parse — any of these fail safely back to
     * a live filesystem scan in enabledModules(), so a corrupt cache can
     * never fatal the application.
     *
     * @return list<Module>|null
     */
    private function loadFromCompiledCache(): ?array
    {
        if (!is_file($this->compiledCachePath)) {
            return null;
        }

        try {
            $data = require $this->compiledCachePath;
        } catch (Throwable) {
            return null;
        }

        if (!is_array($data)) {
            return null;
        }

        $modules = [];
        foreach ($data as $entry) {
            if (!is_array($entry)) {
                return null;
            }

            $modules[] = new Module(
                name: (string) ($entry['name'] ?? ''),
                path: (string) ($entry['path'] ?? ''),
                enabled: (bool) ($entry['enabled'] ?? true),
                version: (string) ($entry['version'] ?? '0.1.0'),
                sortOrder: (int) ($entry['sortOrder'] ?? 100),
                source: (string) ($entry['source'] ?? 'app'),
            );
        }

        return $modules;
    }

    private static function sourcePriority(string $source): int
    {
        return match ($source) {
            'app' => 300,
            'modules' => 200,
            'vendor' => 100,
            default => 0,
        };
    }

    /**
     * @return list<array{moduleFile: string, source: string}>
     */
    private function moduleCandidates(): array
    {
        $candidates = [];

        foreach ($this->globbedModuleFiles('app/*/module.php', 'app') as $candidate) {
            $candidates[] = $candidate;
        }
        foreach ($this->globbedModuleFiles('modules/*/module.php', 'modules') as $candidate) {
            $candidates[] = $candidate;
        }
        foreach ($this->globbedModuleFiles('modules/*/*/module.php', 'modules') as $candidate) {
            $candidates[] = $candidate;
        }
        foreach ($this->composerPackageModuleFiles() as $candidate) {
            $candidates[] = $candidate;
        }

        return $candidates;
    }

    /**
     * @return list<array{moduleFile: string, source: string}>
     */
    private function globbedModuleFiles(string $pattern, string $source): array
    {
        $files = glob(rtrim($this->basePath, '/\\') . '/' . $pattern) ?: [];
        sort($files);

        return array_map(
            static fn (string $file): array => ['moduleFile' => $file, 'source' => $source],
            $files,
        );
    }

    /**
     * @return list<array{moduleFile: string, source: string}>
     */
    private function composerPackageModuleFiles(): array
    {
        $files = glob(rtrim($this->basePath, '/\\') . '/vendor/*/*/composer.json') ?: [];
        sort($files);
        $result = [];

        foreach ($files as $composerFile) {
            $json = json_decode((string) file_get_contents($composerFile), true);
            if (!is_array($json)) {
                continue;
            }

            $extra = is_array($json['extra'] ?? null) ? $json['extra'] : [];
            $marko = is_array($extra['marko'] ?? null) ? $extra['marko'] : [];

            if (($json['type'] ?? null) !== 'zoosper-module'
                || ($marko['module'] ?? false) !== true
            ) {
                continue;
            }

            $moduleFile = dirname($composerFile) . '/module.php';
            if (is_file($moduleFile)) {
                $result[] = ['moduleFile' => $moduleFile, 'source' => 'vendor'];
            }
        }

        return $result;
    }

    /** @return array<string, mixed>|null */
    private function composerPackageMetadata(string $modulePath): ?array
    {
        $composerFile = rtrim($modulePath, '/\\') . '/composer.json';
        if (!is_file($composerFile)) {
            return null;
        }

        $metadata = json_decode((string) file_get_contents($composerFile), true);
        if (!is_array($metadata)) {
            return null;
        }

        $extra = is_array($metadata['extra'] ?? null) ? $metadata['extra'] : [];
        $marko = is_array($extra['marko'] ?? null) ? $extra['marko'] : [];

        if (($metadata['type'] ?? null) !== 'zoosper-module'
            || ($marko['module'] ?? false) !== true
        ) {
            return null;
        }

        return $metadata;
    }

    private function moduleFromCandidate(string $moduleFile, string $source): ?Module
    {
        $metadata = require $moduleFile;
        if (!is_array($metadata)) {
            return null;
        }

        $modulePath = dirname($moduleFile);
        $package = $this->composerPackageMetadata($modulePath);
        $identity = ModulePackageIdentity::fromModule($metadata, basename($modulePath));
        $name = isset($package['name']) && is_string($package['name'])
            ? str_replace('/', '-', $package['name'])
            : (string) ($metadata['name'] ?? $identity?->moduleName ?? basename($modulePath));
        $version = isset($package['version']) && is_string($package['version'])
            ? $package['version']
            : (string) ($metadata['version'] ?? '0.1.0');

        return new Module(
            name: $name,
            path: $modulePath,
            enabled: (bool) ($metadata['enabled'] ?? true),
            version: $version,
            sortOrder: (int) ($metadata['sort_order'] ?? 100),
            source: $source,
        );
    }
}

