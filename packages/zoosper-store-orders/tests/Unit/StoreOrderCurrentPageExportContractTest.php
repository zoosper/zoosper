<?php

declare(strict_types=1);
namespace Zoosper\StoreOrders\Tests\Unit;
it('uses feature owned current page export', function (): void { $root=dirname(__DIR__,4);$toolbar=file_get_contents($root.'/packages/zoosper-admin-grid/src/GridCompactToolbarRenderer.php');$workspace=file_get_contents($root.'/packages/zoosper-store-orders/src/Admin/StoreOrderGridWorkspace.php');$script=file_get_contents($root.'/packages/zoosper-admin-grid/resources/admin/js/grid-compact-workspace.js');expect($toolbar)->not->toBeFalse()->not->toContain('href="/admin/pages/export"')->and($workspace)->not->toBeFalse()->toContain('?grid_export=current')->and($script)->not->toBeFalse()->toContain('store-orders-current-page.csv'); });
