<?php

declare(strict_types=1);

namespace Zoosper\Core\Release;

use Throwable;
use Zoosper\Core\Module\ModuleManifestStatus;

final readonly class ReleaseCheck
{
    /** @param (callable(): array<string, int>)|null $foreignKeyCounts */
    public function __construct(
        private string $basePath,
        private mixed $foreignKeyCounts = null,
    ) {}

    /** @return list<ReleaseCheckResult> */
    public function run(): array
    {
        $results = [
            new ReleaseCheckResult('php', version_compare(PHP_VERSION, '8.5.0', '>='), 'PHP ' . PHP_VERSION . ' (requires >= 8.5)'),
            new ReleaseCheckResult('extension:pdo', extension_loaded('pdo'), extension_loaded('pdo') ? 'PDO loaded' : 'PDO missing'),
            new ReleaseCheckResult('extension:json', extension_loaded('json'), extension_loaded('json') ? 'JSON loaded' : 'JSON missing'),
        ];
        foreach (['var', 'var/cache', 'var/log'] as $relative) {
            $path = $this->basePath . '/' . $relative;
            if (!is_dir($path)) { @mkdir($path, 0775, true); }
            $results[] = new ReleaseCheckResult('writable:' . $relative, is_dir($path) && is_writable($path), $relative . (is_writable($path) ? ' is writable' : ' is not writable'));
        }
        foreach ([
            'admin:css' => 'public/assets/admin/css/admin.css',
            'env-example' => '.env.example',
            'starter-theme' => 'themes/default/theme.php',
            'starter-layout' => 'themes/default/templates/layout.latte',
            'starter-page-view' => 'themes/default/templates/modules/zoosper-page/page/view.latte',
            'starter-theme-css' => 'themes/default/assets/css/app.css',
        ] as $name => $relative) {
            $results[] = new ReleaseCheckResult($name, is_file($this->basePath . '/' . $relative), $relative);
        }
        $results = array_merge($results, $this->moduleFileResults());
        $manifest = (new ModuleManifestStatus($this->basePath))->inspect();
        $results[] = new ReleaseCheckResult('module-manifest', $manifest['status'] === 'fresh', 'status=' . $manifest['status']);
        $foreignKeys = $this->foreignKeyResult();
        if ($foreignKeys !== null) {
            $results[] = $foreignKeys;
        }
        $app = require $this->basePath . '/config/app.php';
        $debug = (bool) ($app['debug'] ?? false);
        $environment = (string) ($app['env'] ?? 'production');
        $safe = $environment !== 'production' || !$debug;
        $results[] = new ReleaseCheckResult('production-debug', $safe, "env={$environment}, debug=" . ($debug ? 'true' : 'false'));
        return $results;
    }


    /** Resolve required files from enabled module identities, never guessed layout paths.
     * @return list<ReleaseCheckResult>
     */
    public function moduleFileResults(): array
    {
        $required = [
            'settings:css' => ['zoosper-settings', 'resources/assets/css/settings-workspace.css'],
            'settings:js' => ['zoosper-settings', 'resources/assets/js/settings-workspace.js'],
            'starter-command' => ['zoosper-page', 'src/Console/StarterSiteInstallCommand.php'],
            'session-settings' => ['zoosper-session', 'config/settings/session.php'],
        ];
        $results = [];
        try {
            $paths = [];
            foreach ((new \Zoosper\Core\Module\ModuleRegistry($this->basePath))->enabledModules() as $module) {
                $paths[$module->name] = $module->path;
            }
            foreach ($required as $name => [$identity, $relative]) {
                $path = isset($paths[$identity]) ? $paths[$identity] . '/' . $relative : null;
                $results[] = new ReleaseCheckResult(
                    $name,
                    $path !== null && is_file($path),
                    $path ?? 'Required enabled module missing: ' . $identity,
                );
            }
        } catch (Throwable $exception) {
            foreach ($required as $name => $_definition) {
                $results[] = new ReleaseCheckResult($name, false, 'Module inspection failed: ' . $exception->getMessage());
            }
        }
        return $results;
    }

    public function foreignKeyResult(): ?ReleaseCheckResult
    {
        if (!is_callable($this->foreignKeyCounts)) {
            return null;
        }

        try {
            $counts = ($this->foreignKeyCounts)();
            $present = $counts['present'] ?? 0;
            $add = $counts['add'] ?? 0;
            $mismatch = $counts['mismatch'] ?? 0;
            $sqliteRebuild = $counts['sqlite_rebuild_required'] ?? 0;

            return new ReleaseCheckResult(
                'foreign-keys',
                $add === 0 && $mismatch === 0 && $sqliteRebuild === 0,
                "present={$present}, add={$add}, mismatch={$mismatch}, sqlite_rebuild_required={$sqliteRebuild}",
            );
        } catch (Throwable $exception) {
            return new ReleaseCheckResult(
                'foreign-keys',
                false,
                'inspection failed: ' . $exception->getMessage(),
            );
        }
    }
}










