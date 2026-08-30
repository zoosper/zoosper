<?php

declare(strict_types=1);

it('composes the compact shell account Dashboard and Grid without fabricated capabilities', function (): void {
    $root = dirname(__DIR__, 5);
    $layout = (string) file_get_contents($root . '/themes/admin/default/templates/layout.php');
    $adminLayout = (string) file_get_contents($root . '/app/zoosper-admin/src/Layout/AdminLayout.php');
    $dashboard = (string) file_get_contents($root . '/app/zoosper-admin/resources/views/dashboard/index.php');
    $shell = (string) file_get_contents($root . '/app/zoosper-admin/resources/assets/css/admin-shell.css');
    $components = (string) file_get_contents($root . '/app/zoosper-admin/resources/assets/css/admin-components.css');
    $grid = (string) file_get_contents($root . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css');

    expect($layout)->toContain('class="admin-account-menu"')
        ->toContain("<?= \$logoutFormHtml ?? '' ?>")
        ->toContain('data-admin-theme-selector')
        ->not->toMatch('/\son[a-z]+\s*=/i')
        ->not->toMatch('/\sstyle\s*=/i')
        ->and($adminLayout)->toContain('class="admin-account-logout-form"')
        ->toContain('method="post"')
        ->toContain('$this->csrf->token()')
        ->and($dashboard)->toContain('dashboard-personalisation__body')
        ->toContain('$availableWidgets')
        ->not->toContain('Recent activity')
        ->and($shell)->toContain('/* Fable bulk pass: calmer shell hierarchy')
        ->and($components)->toContain('/* Fable bulk pass: Dashboard hierarchy')
        ->and($grid)->toContain('/* Fable bulk pass: clearer Grid command hierarchy')
        ->toContain('position: sticky')
        ->not->toContain('javascript:');
});
