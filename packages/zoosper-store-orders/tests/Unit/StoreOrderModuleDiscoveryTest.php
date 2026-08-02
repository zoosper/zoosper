<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

it('ships the package module marker required by Composer module discovery', function (): void {
    $root = dirname(__DIR__, 4);
    $moduleFile = $root . '/packages/zoosper-store-orders/module.php';

    expect($moduleFile)->toBeFile()
        ->and(require $moduleFile)->toBeArray();
});
