<?php

declare(strict_types=1);

it('keeps protected coordinator construction in the shared Admin Grid package', function (): void {
    $root = dirname(__DIR__, 5);
    $factory = file_get_contents(
        $root . '/packages/zoosper-admin-grid/src/BulkAction/GridBulkHttpCoordinatorFactory.php',
    );
    $bindings = file_get_contents(
        $root . '/packages/zoosper-admin-grid/src/BulkAction/GridBulkHostBindings.php',
    );

    expect($factory)->not->toBeFalse();
    expect($bindings)->not->toBeFalse();
    expect($factory)->toContain('new GridBulkHttpCoordinator(');
    expect($factory)->not->toContain('Zoosper\Page');
    expect($factory)->not->toContain('StoreOrder');
    expect($bindings)->not->toContain('$_POST');
    expect($bindings)->not->toContain('$_SESSION');
});










