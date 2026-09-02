<?php
declare(strict_types=1);
namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridCompactWorkspaceRenderer;

it('derives current-page export metadata from each feature action', function (): void {
    $renderer = new GridCompactWorkspaceRenderer();
    $pages = $renderer->render(featureUrlState(), '/admin/pages');
    $orders = $renderer->render(featureUrlState(), '/admin/store-orders');
    $media = $renderer->render(featureUrlState(), '/admin/media', null, false);

    expect($pages)->toContain('href="/admin/pages/export"')
        ->toContain('data-grid-current-page-filename="pages-current-page.csv"')
        ->and($orders)->toContain('href="/admin/store-orders/export"')
        ->toContain('data-grid-current-page-filename="store-orders-current-page.csv"')
        ->and($media)->not->toContain('data-grid-export href=')
        ->toContain('data-grid-current-page-filename="media-current-page.csv"');
});

it('keeps shared source free of Page and Store Orders presentation defaults', function (): void {
    $root = dirname(__DIR__, 4);
    $toolbar = (string) file_get_contents($root . '/packages/zoosper-admin-grid/src/GridCompactToolbarRenderer.php');
    $workspace = (string) file_get_contents($root . '/packages/zoosper-admin-grid/src/GridCompactWorkspaceRenderer.php');
    $script = (string) file_get_contents($root . '/packages/zoosper-admin-grid/resources/admin/js/grid-compact-workspace.js');

    expect($toolbar . $workspace)->not->toContain("'/admin/pages'")
        ->not->toContain("'/admin/pages/export'")
        ->and($script)->not->toContain("anchor.download='store-orders-current-page.csv'")
        ->toContain("root.dataset.gridCurrentPageFilename||'grid-current-page.csv'");
});
