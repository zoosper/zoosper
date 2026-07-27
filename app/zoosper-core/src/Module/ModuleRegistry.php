<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

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
 * events, entity-save listeners, admin menu, translations, admin assets), one
 * memoized call here removes the redundant re-scan from ALL of them for the
 * lifetime of the request/process. Discovery is pure (no side effects), so
 * caching is safe.
 */
final class ModuleRegistry
{
    /** @var list<Module>|null Cached result of the first enabledModules() call. */
    private ?array $cachedModules = null;

    public function __construct(private readonly string $basePath)
    {
    }

    /** @return list<Module> */
    public function enabledModules(): array
    {
        if ($this->cachedModules !== null) {
            return $this->cachedModules;
        }

        $modules = [];
        $seenRealPaths = [];
        $seenNames = [];

        foreach ($this->moduleCandidates() as $candidate) {
            $module = $this->moduleFromCandidate($candidate['moduleFile'], $candidate['source']);
            if ($module === null || !$module->enabled) {
                continue;
            }

            $realPath = realpath($module->path) ?: $module->path;
            $dedupeKey = strtolower($module->name);

            if (isset($seenRealPaths[$realPath]) || isset($seenNames[$dedupeKey])) {
                continue;
            }

            $seenRealPaths[$realPath] = true;
            $seenNames[$dedupeKey] = true;
            $modules[] = $module;
        }

        usort($modules, static function (Module $a, Module $b): int {
            return [$a->sortOrder, $a->name] <=> [$b->sortOrder, $b->name];
        });

        $this->cachedModules = $modules;

        return $this->cachedModules;
    }

    /**
     * Force the next enabledModules() call to re-scan the filesystem. Not used
     * in normal request handling (module state cannot change mid-request); it
     * exists for long-lived worker processes (Swoole/FrankenPHP) or tests that
     * need to observe a changed module set within the same registry instance.
     */
    public function clearCache(): void
    {
        $this->cachedModules = null;
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
        foreach ($this->globbedModuleFiles('packages/*/module.php', 'packages') as $candidate) {
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

            $extra = is_array($json['extra']['zoosper'] ?? null) ? $json['extra']['zoosper'] : [];
            $modulePath = (string) ($extra['module'] ?? '');
            if ($modulePath === '') {
                continue;
            }

            $moduleFile = dirname($composerFile) . '/' . ltrim($modulePath, '/\\');
            if (is_file($moduleFile)) {
                $result[] = ['moduleFile' => $moduleFile, 'source' => 'vendor'];
            }
        }

        return $result;
    }

    private function moduleFromCandidate(string $moduleFile, string $source): ?Module
    {
        $metadata = require $moduleFile;
        if (!is_array($metadata)) {
            return null;
        }

        $modulePath = dirname($moduleFile);
        $identity = ModulePackageIdentity::fromModule($metadata, basename($modulePath));
        $name = (string) ($metadata['name'] ?? $identity?->moduleName ?? basename($modulePath));

        return new Module(
            name: $name,
            path: $modulePath,
            enabled: (bool) ($metadata['enabled'] ?? true),
            version: (string) ($metadata['version'] ?? '0.1.0'),
            sortOrder: (int) ($metadata['sort_order'] ?? 100),
            source: $source,
        );
    }
}
