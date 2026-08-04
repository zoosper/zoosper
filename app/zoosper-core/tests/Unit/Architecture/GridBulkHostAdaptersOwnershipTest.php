<?php

declare(strict_types=1);

it('keeps reusable host adapters free of feature dependencies and globals', function (): void {
    $root = dirname(__DIR__, 5);
    $directory = $root . '/packages/zoosper-admin-grid/src/BulkAction';
    $source = '';
    foreach (glob($directory . '/*.php') ?: [] as $file) {
        $source .= (string) file_get_contents($file);
    }
    expect($source)->not->toContain('Zoosper\\Page')
        ->not->toContain('StoreOrder')
        ->not->toContain('$_POST')
        ->not->toContain('$_SESSION')
        ->not->toContain('header(');
});
