<?php

declare(strict_types=1);

it('registers the Admin-owned component layer after shell tokens and before feature assets', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/app/zoosper-admin/config/admin_assets.php';
    $assets = $manifest['assets'];

    expect(is_file($root . '/app/zoosper-admin/resources/assets/css/admin-components.css'))->toBeTrue()
        ->and($assets['zoosper-admin-components-style'])
        ->toMatchArray([
            'type' => 'style',
            'path' => '/asset/zoosper-admin/css/admin-components.css?v=318181950bb2',
            'sort_order' => 16,
        ])
        ->and($assets['zoosper-admin-shell-style']['sort_order'])
        ->toBeLessThan($assets['zoosper-admin-components-style']['sort_order'])
        ->and($assets['zoosper-admin-components-style']['sort_order'])
        ->toBeLessThan($assets['zoosper-admin-messages-style']['sort_order']);
});

it('provides fluid theme-aware components with responsive and reduced-motion behaviour', function (): void {
    $root = dirname(__DIR__, 5);
    $css = (string) file_get_contents($root . '/app/zoosper-admin/resources/assets/css/admin-components.css');

    expect($css)
        ->toContain(':root[data-admin-theme="dark"]')
        ->toContain('grid-template-columns: repeat(auto-fit, minmax(min(100%, 17rem), 1fr))')
        ->toContain('.page-header')
        ->toContain('.admin-page-actions')
        ->toContain('justify-content: flex-end')
        ->toContain('.admin-card-grid')
        ->toContain('.admin-alert--success')
        ->toContain('.admin-badge--danger')
        ->toContain('.admin-table-scroll')
        ->toContain('.admin-empty-state')
        ->toContain('.dashboard-widget-grid')
        ->toContain('.dashboard-widget')
        ->toContain('@media (max-width: 680px)')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->not->toMatch('/javascript\s*:/i')
        ->not->toContain('expression(');
});

it('keeps shared theme components semantic and free from inline production behaviour', function (): void {
    $root = dirname(__DIR__, 5);
    $componentPaths = [
        '/themes/admin/default/templates/components/card.php',
        '/themes/admin/default/templates/components/error.php',
        '/themes/admin/default/templates/components/table.php',
        '/themes/admin/default/templates/components/grid/pagination.php',
        '/themes/admin/default/templates/partials/components/grid/pagination.php',
        '/themes/admin/default/templates/modules/zoosper-admin/dashboard/index.php',
    ];
    $templates = '';
    foreach ($componentPaths as $path) {
        $templates .= (string) file_get_contents($root . $path);
    }

    expect($templates)
        ->toContain('class="card__header"')
        ->toContain('class="card__body"')
        ->toContain('role="alert"')
        ->toContain('class="admin-table-scroll"')
        ->toContain('scope="col"')
        ->toContain('aria-live="polite"')
        ->toContain('class="page-header dashboard-hero"')
        ->not->toMatch('/\son[a-z]+\s*=/i')
        ->not->toMatch('/\sstyle\s*=/i')
        ->not->toContain('<script');
});
