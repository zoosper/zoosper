<?php

declare(strict_types=1);

it('keeps Dashboard widgets server rendered escaped and free from inline behaviour', function (): void {
    $root = dirname(__DIR__, 5);
    $moduleView = (string) file_get_contents($root . '/app/zoosper-admin/resources/views/dashboard/index.php');
    $themeView = (string) file_get_contents($root . '/themes/admin/default/templates/modules/zoosper-admin/dashboard/index.php');
    $css = (string) file_get_contents($root . '/app/zoosper-admin/resources/assets/css/admin-components.css');

    expect($moduleView)->toBe($themeView)
        ->toContain('class="dashboard-widget-grid"')
        ->toContain('aria-labelledby="dashboard-widgets-title"')
        ->toContain('<?= $e($widget->code) ?>')
        ->toContain('<?= $e($widget->title) ?>')
        ->toContain('<?= $e($widget->value) ?>')
        ->toContain('<?= $e($widget->description) ?>')
        ->not->toMatch('/\son[a-z]+\s*=/i')
        ->not->toMatch('/\sstyle\s*=/i')
        ->not->toContain('<script')
        ->and($css)->toContain('.dashboard-widget-grid')
        ->toContain('@media (max-width: 680px)');
});

it('uses only the module-discovered permission-filtered widget boundary', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/DashboardController.php');

    expect($controller)->toContain('$this->dashboard->forUser($user)')
        ->toContain("template: 'zoosper-admin::dashboard/index'")
        ->not->toContain('DashboardQuickLinks')
        ->not->toContain('/admin/users');
});










