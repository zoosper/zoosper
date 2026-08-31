<?php

declare(strict_types=1);

it('keeps the shared HTTP boundary free of Page and Store Orders dependencies', function (): void {
    $root = dirname(__DIR__, 5);
    $directory = $root . '/packages/zoosper-admin-grid/src/BulkAction';
    $source = '';
    foreach (glob($directory . '/*.php') ?: [] as $file) {
        $source .= (string) file_get_contents($file);
    }
    expect($source)->not->toContain('Zoosper\Page')
        ->not->toContain('StoreOrder')
        ->not->toContain('$_POST')
        ->not->toContain('session_start');
});










