<?php

declare(strict_types=1);

it('preserves requested Store Orders page through shared workspace criteria', function (): void {
    $root = dirname(__DIR__, 4);
    $normaliser = file_get_contents(
        $root . '/packages/zoosper-admin-grid/src/GridStateNormaliser.php',
    );
    $controller = file_get_contents(
        $root . '/packages/zoosper-store-orders/src/Admin/StoreOrderAdminController.php',
    );
    $mapper = file_get_contents(
        $root . '/packages/zoosper-store-orders/src/Api/StoreOrderRequestMapper.php',
    );

    expect($normaliser)->not->toBeFalse()
        ->and($controller)->not->toBeFalse()
        ->and($mapper)->not->toBeFalse()
        ->and($normaliser)->toContain(<<<'PHP'
'page' => $normalised['page']
PHP)
        ->and($controller)->toContain(<<<'PHP'
page: $state->criteria->pager->page
PHP)
        ->and($mapper)->toContain(<<<'PHP'
'page' => $query->page
PHP);
});
