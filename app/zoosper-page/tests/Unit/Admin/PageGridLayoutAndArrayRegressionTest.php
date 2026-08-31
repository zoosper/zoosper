<?php

declare(strict_types=1);
namespace Zoosper\Page\Tests\Unit\Admin;

test('Grid layout assets are Admin-owned and Grid URLs preserve arrays', function (): void {
    $root=dirname(__DIR__,5);
    $renderer=(string)file_get_contents($root.'/packages/zoosper-grid/src/GridHtmlRenderer.php');
    $workspace=(string)file_get_contents($root.'/packages/zoosper-admin-grid/src/GridCompactWorkspaceRenderer.php');
    $script=(string)file_get_contents($root.'/app/zoosper-admin/resources/assets/js/zoosper-grid-columns.js');
    expect($renderer)->toContain('array_walk_recursive')->not->toContain("site_id=Array");
    expect($workspace)->toContain('private function flatten');
    expect($script)->toContain("workspace.closest('main')")->toContain(':nth-child');
    expect(is_file($root.'/app/zoosper-admin/resources/assets/css/zoosper-grid-compact.css'))->toBeTrue();
});










