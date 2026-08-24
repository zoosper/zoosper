<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

it('composes Store Orders through the shared Admin Grid workspace', function (): void {
    $root = dirname(__DIR__, 4);
    $workspace = file_get_contents(
        $root . '/packages/zoosper-store-orders/src/Admin/StoreOrderGridWorkspace.php',
    );
    $controller = file_get_contents(
        $root . '/packages/zoosper-store-orders/src/Admin/StoreOrderAdminController.php',
    );
    $composer = json_decode(
        (string) file_get_contents($root . '/packages/zoosper-store-orders/composer.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($workspace)->not->toBeFalse()
        ->and($workspace)->toContain('GridViewStateResolver')
        ->and($workspace)->toContain('GridCompactWorkspaceRenderer')
        ->and($workspace)->toContain("GRID_KEY = 'store.orders'")
        ->and($controller)->not->toBeFalse()
        ->and($controller)->toContain('StoreOrderGridWorkspace')
        ->and($controller)->toContain('renderBody(')
        ->and($composer['require'])->toHaveKey('zoosper/admin-grid', 'dev-dev');
});

it('keeps the remote request driven by resolved workspace scope and page size', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents(
        $root . '/packages/zoosper-store-orders/src/Admin/StoreOrderAdminController.php',
    );

    expect($source)->not->toBeFalse()
        ->and($source)->toContain('$state->criteria->filters[\'store_code\']')
        ->and($source)->toContain('$state->criteria->filters[\'kiosk_website_id\']')
        ->and($source)->toContain('pageSize: $state->criteria->pager->pageSize');
});


it('uses the shell title once and publishes feature-owned page-size choices', function (): void {
    $root = dirname(__DIR__, 4);
    $controller = (string) file_get_contents(
        $root . '/packages/zoosper-store-orders/src/Admin/StoreOrderAdminController.php',
    );
    $workspace = (string) file_get_contents(
        $root . '/packages/zoosper-store-orders/src/Admin/StoreOrderGridWorkspace.php',
    );

    expect($controller)->toContain("layout->render('Store Orders'")
        ->not->toContain('<h1>Store Orders</h1>')
        ->toContain('role="alert"')
        ->and($workspace)->toContain('$apiDefinition = StoreOrderGrid::definition()')
        ->toContain('pageSizeOptions: $apiDefinition->pageSizes');
});
