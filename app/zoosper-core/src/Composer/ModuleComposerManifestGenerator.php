<?php

declare(strict_types=1);

namespace Zoosper\Core\Composer;

use RuntimeException;

/**
 * Generates package-ready composer.json manifests for enabled Zoosper modules.
 *
 * The generated manifests are intentionally conservative. They make each module
 * self-describing and Composer-package-ready without physically moving the module
 * out of the monorepo yet.
 */
final readonly class ModuleComposerManifestGenerator
{
    /** @var array<string, list<string>> */
    private const PACKAGE_DEPENDENCIES = [
        'zoosper/admin' => ['zoosper/core', 'zoosper/auth', 'zoosper/theme'],
        'zoosper/api' => ['zoosper/core', 'zoosper/auth', 'zoosper/page', 'zoosper/site'],
        'zoosper/auth' => ['zoosper/core'],
        'zoosper/mail' => ['zoosper/core'],
        'zoosper/media' => ['zoosper/core', 'zoosper/admin', 'zoosper/auth'],
        'zoosper/page' => ['zoosper/core', 'zoosper/admin', 'zoosper/site', 'zoosper/theme'],
        'zoosper/site' => ['zoosper/core'],
        'zoosper/theme' => ['zoosper/core'],
        'zoosper/two-factor' => ['zoosper/core', 'zoosper/auth', 'zoosper/admin'],
        'zoosper/url-rewrite' => ['zoosper/core'],
    ];

    public function __construct(private string $basePath)
    {
    }

    /**
     * Generate composer.json manifests for modules that have a src/ directory.
     *
     * @return list<string> Relative paths written.
     */
    public function generate(bool $overwrite = false): array
    {
        $written = [];
        foreach ($this->moduleFiles() as $moduleFile) {
            $moduleDir = dirname($moduleFile);
            if (!is_dir($moduleDir . '/src')) {
                continue;
            }

            $module = require $moduleFile;
            if (!is_array($module) || ($module['enabled'] ?? true) === false) {
                continue;
            }

            $identity = ModulePackageIdentity::fromModule($module, basename($moduleDir));
            if ($identity === null) {
                continue;
            }

            $target = $moduleDir . '/composer.json';
            if (is_file($target) && !$overwrite) {
                continue;
            }

            file_put_contents($target, json_encode($this->manifest($identity, $moduleDir), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
            $written[] = $this->relativePath($target);
        }

        return $written;
    }

    /**
     * @return array<string, mixed>
     */
    public function manifest(ModulePackageIdentity $identity, string $moduleDir): array
    {
        $require = ['php' => '^8.5'];
        if ($identity->packageName === 'zoosper/core') {
            $require['ext-pdo'] = '*';
        }

        foreach (self::PACKAGE_DEPENDENCIES[$identity->packageName] ?? [] as $package) {
            $require[$package] = '*@dev';
        }

        $manifest = [
            'name' => $identity->packageName,
            'description' => $identity->moduleName . ' module for Zoosper CMS.',
            'type' => 'zoosper-module',
            'license' => 'MIT',
            'require' => $require,
            'autoload' => [
                'psr-4' => [
                    $identity->namespace => 'src/',
                ],
            ],
            'extra' => [
                'zoosper' => [
                    'module' => 'module.php',
                    'name' => $identity->moduleName,
                ],
            ],
        ];

        if (is_dir($moduleDir . '/tests')) {
            $manifest['autoload-dev'] = [
                'psr-4' => [
                    $identity->namespace . 'Tests\\' => 'tests/',
                ],
            ];
        }

        return $manifest;
    }

    /** @return list<string> */
    private function moduleFiles(): array
    {
        $files = array_merge(
            glob($this->basePath . '/app/*/module.php') ?: [],
            glob($this->basePath . '/modules/*/module.php') ?: [],
            glob($this->basePath . '/modules/*/*/module.php') ?: [],
        );
        sort($files);

        return $files;
    }

    private function relativePath(string $path): string
    {
        return ltrim(str_replace($this->basePath, '', $path), '/\\');
    }
}
