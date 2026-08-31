<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('column-specific filters follow their matching column checkbox', function (): void {
    $root = dirname(__DIR__, 5);
    $css = (string) file_get_contents(
        $root . '/app/zoosper-admin/resources/assets/css/zoosper-grid.css',
    );
    $renderer = (string) file_get_contents(
        $root . '/packages/zoosper-admin-grid/src/GridCompactWorkspaceRenderer.php',
    );

    expect($css)
        ->toContain('BEGIN GRID COLUMN FILTER VISIBILITY')
        ->toContain('[value="slug"]:not(:checked)')
        ->toContain('[data-grid-filter-columns="slug"]')
        ->toContain('[value="status"]:not(:checked)')
        ->toContain('[data-grid-filter-columns="status"]')
        ->toContain('[value="site_name"]:not(:checked)')
        ->toContain('[data-grid-filter-columns="site_name"]');

    expect($renderer)
        ->toContain("'slug' => 'slug'")
        ->toContain("'status' => 'status'")
        ->toContain("'site_id' => 'site_name'");
});










