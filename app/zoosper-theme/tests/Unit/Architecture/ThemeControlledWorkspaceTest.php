<?php

declare(strict_types=1);

it('ships equivalent Theme-owned workspaces without expanding Theme capabilities', function (): void {
    $root = dirname(__DIR__, 5);
    $paths = [
        $root . '/app/zoosper-theme/resources/views/admin/themes/index.php',
        $root . '/themes/admin/default/templates/modules/zoosper-theme/admin/themes/index.php',
    ];
    foreach ($paths as $path) {
        $view = (string) file_get_contents($path);
        expect($view)->toContain('class="theme-workspace"')
            ->toContain('Design / Themes')
            ->toContain('<h1>Frontend Themes</h1>')
            ->toContain('class="card theme-catalogue"')
            ->toContain('aria-label="Installed frontend themes" tabindex="0"')
            ->toContain('class="card theme-assignment-card"')
            ->toContain('method="post" action="<?= $e($assignUrl) ?>"')
            ->toContain('name="_csrf_token" value="<?= $e($csrfToken) ?>"')
            ->toContain('name="site_id" value="<?= $e($site->id) ?>"')
            ->toContain('name="theme_code"')
            ->not->toContain('action="/admin/themes/assign"')
            ->not->toContain('Install theme')
            ->not->toContain('Delete theme')
            ->not->toContain('Clone theme')
            ->not->toContain('<script');
    }
    $controller = (string) file_get_contents($root . '/app/zoosper-theme/src/Admin/Controller/ThemeAdminController.php');
    $routes = require $root . '/app/zoosper-theme/config/admin_routes.php';
    expect($controller)->toContain("title: '',")
        ->toContain("'themes' => \$this->themes->all()")
        ->toContain("'sites' => \$this->sites->allActive()")
        ->toContain('$this->assignment->assign($siteId, $themeCode)')
        ->toContain("'site.theme.updated'")
        ->and(array_column($routes, 'permission', 'path')['/admin/themes'] ?? null)->toBe('settings.manage')
        ->and(array_column($routes, 'method', 'path')['/admin/themes/assign'] ?? null)->toBe('POST');
});
