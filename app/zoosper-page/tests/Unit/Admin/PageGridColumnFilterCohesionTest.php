<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('Page Grid column state controls table and column-specific filters', function (): void {
    $root = dirname(__DIR__, 5);
    $query = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/PageGridQueryState.php');
    $workspace = (string) file_get_contents($root . '/packages/zoosper-admin-grid/src/GridCompactWorkspaceRenderer.php');
    $table = (string) file_get_contents($root . '/packages/zoosper-grid/src/GridHtmlRenderer.php');

    expect($query)->toContain("array_key_exists('visible_columns', \$query)");
    expect($workspace)->toContain("'slug' => 'slug'")
        ->toContain("'status' => 'status'")
        ->toContain("'site_id' => 'site_name'");
    expect($table)->toContain('data-grid-column');
});
