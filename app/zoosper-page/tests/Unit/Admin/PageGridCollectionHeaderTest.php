<?php
declare(strict_types=1);

it('owns the clean Pages collection header outside the shared Grid package', function (): void {
    $root = dirname(__DIR__, 5);
    $view = (string) file_get_contents($root . '/app/zoosper-page/resources/views/admin/pages/index.php');
    $css = (string) file_get_contents($root . '/app/zoosper-page/resources/admin/css/page-grid-workspace.css');
    $assets = require $root . '/app/zoosper-page/config/admin_assets.php';
    $roots = require $root . '/app/zoosper-page/config/assets.php';
    $version = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", $css)), 0, 12);

    expect($view)->toContain('page-grid-index__header')
        ->toContain('Content / Pages')
        ->toContain('Manage and publish pages across all your sites.')
        ->toContain('>Export</a>')
        ->toContain('>Create page</a>')
        ->toContain('$gridHtml')
        ->not->toContain('class="toolbar"')
        ->and($css)->toContain('font-weight: 600;')
        ->toContain('.page-grid-index > [data-grid-workspace]')
        ->toContain('@media (max-width: 48rem)')
        ->and($roots['zoosper-page'] ?? null)->toBe($root . '/app/zoosper-page/resources/admin')
        ->and($assets['assets']['zoosper-page-grid-workspace-style']['path'] ?? null)
        ->toBe('/asset/zoosper-page/css/page-grid-workspace.css?v=' . $version);
});

it('does not move Page collection presentation into the shared Grid package', function (): void {
    $root = dirname(__DIR__, 5);
    $shared = (string) file_get_contents($root . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css');

    expect($shared)->not->toContain('.page-grid-index')
        ->not->toContain('Content / Pages');
});
