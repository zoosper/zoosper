<?php

declare(strict_types=1);

namespace Zoosper\Core\Release;

use Zoosper\Core\Module\ModuleManifestStatus;

final readonly class ReleaseCheck
{
    public function __construct(private string $basePath) {}

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
            'settings:css' => 'app/zoosper-settings/resources/assets/css/settings-workspace.css',
            'settings:js' => 'app/zoosper-settings/resources/assets/js/settings-workspace.js',
            'admin:css' => 'public/assets/admin/css/admin.css',
            'env-example' => '.env.example',
            'starter-theme' => 'themes/default/theme.php',
            'starter-layout' => 'themes/default/templates/layout.latte',
            'starter-page-view' => 'themes/default/templates/modules/zoosper-page/page/view.latte',
            'starter-theme-css' => 'themes/default/assets/css/app.css',
            'starter-command' => 'app/zoosper-page/src/Console/StarterSiteInstallCommand.php',
            'session-settings' => 'app/zoosper-session/config/settings/session.php',
        ] as $name => $relative) {
            $results[] = new ReleaseCheckResult($name, is_file($this->basePath . '/' . $relative), $relative);
        }
        $manifest = (new ModuleManifestStatus($this->basePath))->inspect();
        $results[] = new ReleaseCheckResult('module-manifest', $manifest['status'] === 'fresh', 'status=' . $manifest['status']);
        $app = require $this->basePath . '/config/app.php';
        $debug = (bool) ($app['debug'] ?? false);
        $environment = (string) ($app['env'] ?? 'production');
        $safe = $environment !== 'production' || !$debug;
        $results[] = new ReleaseCheckResult('production-debug', $safe, "env={$environment}, debug=" . ($debug ? 'true' : 'false'));
        return $results;
    }
}
