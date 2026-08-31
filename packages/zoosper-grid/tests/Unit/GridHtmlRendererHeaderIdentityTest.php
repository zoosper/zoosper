<?php

declare(strict_types=1);

namespace Zoosper\Grid\Tests\Unit;

it('emits a data Grid column key from every table header branch', function (): void {
    $root = dirname(__DIR__, 4);
    $path = $root . '/packages/zoosper-grid/src/GridHtmlRenderer.php';
    $source = file_get_contents($path);

    expect($source)->not->toBeFalse()
        ->and($source)->toContain('data-grid-column')
        ->and($source)->not->toMatch('/(?:return|\$html\s*\.=)\s*[\'\"]<th(?![^>]*data-grid-column)/');
});

it('keeps header identity escaped and key driven', function (): void {
    $root = dirname(__DIR__, 4);
    $source = (string) file_get_contents($root . '/packages/zoosper-grid/src/GridHtmlRenderer.php');

    expect($source)->toContain('$this->e($column->key)');
});











