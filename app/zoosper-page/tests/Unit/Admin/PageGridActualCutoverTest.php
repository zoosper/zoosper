<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('live Page controller is wired to compact workspace instead of legacy filter chrome', function (): void {
    $root=dirname(__DIR__,5);
    $controller=(string)file_get_contents($root.'/app/zoosper-page/src/Admin/Controller/PageAdminController.php');
    $factory=(string)file_get_contents($root.'/app/zoosper-page/config/controllers.php');
    $assets=require $root.'/packages/zoosper-admin-grid/config/admin_assets.php';
    expect($controller)->toContain('PageGridWorkspace')->toContain('renderBody(');
    expect($factory)->toContain('GridCompactWorkspaceRenderer')->toContain('PageGridSiteFilter');
    expect(array_column($assets['stylesheets'],'path'))->toContain('resources/admin/css/grid-compact-workspace.css');
    expect(array_column($assets['scripts'],'path'))->toContain('resources/admin/js/grid-compact-workspace.js');
});
