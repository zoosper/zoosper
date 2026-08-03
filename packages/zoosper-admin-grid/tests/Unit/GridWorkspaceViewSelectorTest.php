<?php

declare(strict_types=1);

use Zoosper\AdminGrid\GridCompactToolbarRenderer;

it('renders a selectable saved-view collection and marks the active view', function (): void {
    $html=(new GridCompactToolbarRenderer())->render('View1',false,20,2,'/admin/store-orders/export',[
        ['id'=>7,'name'=>'View1','state'=>[],'is_default'=>false],
        ['id'=>9,'name'=>'Pending','state'=>[],'is_default'=>true],
    ],7,'/admin/store-orders');
    expect($html)->toContain('data-grid-view-selector')
        ->toContain('value="/admin/store-orders?bookmark_id=7" selected')
        ->toContain('Pending (default)')
        ->not->toContain('data-grid-save-view');
});

it('publishes selector assets', function (): void {
    $root=dirname(__DIR__,4);$assets=require $root.'/packages/zoosper-admin-grid/config/admin_assets.php';
    expect($assets['assets'])->toHaveKeys(['zoosper-admin-grid-view-selector-style','zoosper-admin-grid-view-selector-script']);
});
