<?php
declare(strict_types=1);
it('ships the feature-owned Settings hierarchy without changing scoped mutations', function (): void {
    $root = dirname(__DIR__, 3);
    $view = (string) file_get_contents($root . '/resources/views/admin/settings/index.php');
    $css = (string) file_get_contents($root . '/resources/assets/css/settings-workspace.css');
    $responder = (string) file_get_contents($root . '/src/Admin/SettingsCatalogueResponder.php');
    $controller = (string) file_get_contents($root . '/src/Controller/SettingsCatalogueController.php');
    $routes = require $root . '/config/admin_routes.php';
    $assets = require $root . '/config/admin_assets.php';
    expect($view)->toContain('System / Settings')->toContain('Manage module-owned configuration across the active scope.')->toContain('settings-heading-copy')->toContain('settings-search')->toContain('name="_csrf_token"')
        ->and($css)->toContain('Phase 12L: controlled Settings hierarchy refinement.')->toContain('.settings-eyebrow')->toContain('@media(prefers-contrast:more)')
        ->and($responder)->toContain("title: '',")->toContain("template: 'zoosper-settings::admin/settings/index'")->toContain("'scopeLabel' => \$selection['label']")
        ->and($controller)->toContain('$this->mutations->save(')->toContain('$this->mutations->clear(')
        ->and($assets['zoosper-settings-workspace-style']['screens'] ?? [])->toBe(['settings'])
        ->and(array_column($routes, 'permission', 'path')['/admin/settings'] ?? null)->toBe('settings.manage')
        ->and(array_column($routes, 'method', 'path')['/admin/settings/save'] ?? null)->toBe('POST')
        ->and(array_column($routes, 'method', 'path')['/admin/settings/clear'] ?? null)->toBe('POST');
});
