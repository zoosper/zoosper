<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

it('reads Store Orders pagination through resolved workspace criteria', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents(
        $root . '/packages/zoosper-store-orders/src/Admin/StoreOrderAdminController.php',
    );

    expect($source)->not->toBeFalse()
        ->and($source)->toContain('page: $state->criteria->pager->page,')
        ->and($source)->toContain('pageSize: $state->criteria->pager->pageSize,')
        ->and($source)->not->toContain('page: $criteria->pager->page,')
        ->and($source)->not->toContain('pageSize: $criteria->pager->pageSize,');
});
