<?php

declare(strict_types=1);

it('keeps Page bulk backend composition feature-owned and browser-inactive', function (): void {
    $root = dirname(__DIR__, 5);
    $backend = file_get_contents($root . '/app/zoosper-page/src/Admin/BulkAction/PageBulkActionBackend.php');
    $declarations = file_get_contents($root . '/app/zoosper-page/src/Admin/PageGridBulkActions.php');

    expect($backend)->not->toBeFalse();
    expect($declarations)->not->toBeFalse();
    expect($backend)->not->toContain('$_POST');
    expect($backend)->not->toContain('$_SESSION');
    expect($backend)->not->toContain('header(');
    expect($declarations)->toContain('serverDefinitions');
    expect(substr_count($declarations, "id: 'export.selected'"))->toBe(1);
});
