<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Architecture;

test('admin grid persistence is owned by its focused runtime module', function (): void {
    $basePath = dirname(__DIR__, 5);
    expect($basePath . '/packages/zoosper-admin-grid/composer.json')->toBeFile();
    expect($basePath . '/packages/zoosper-admin-grid/config/db_schema.php')->toBeFile();
    expect($basePath . '/app/zoosper-admin/src/Grid/GridPreferenceRepository.php')->not->toBeFile();
    expect($basePath . '/app/zoosper-admin/src/Grid/GridBookmarkRepository.php')->not->toBeFile();
});

test('admin module no longer declares admin grid persistence tables', function (): void {
    $basePath = dirname(__DIR__, 5);
    $schema = require $basePath . '/app/zoosper-admin/config/db_schema.php';
    expect($schema['tables'] ?? [])->not->toHaveKeys(['admin_grid_preferences', 'admin_grid_bookmarks']);
    $owned = require $basePath . '/packages/zoosper-admin-grid/config/db_schema.php';
    expect($owned['tables'] ?? [])->toHaveKeys(['admin_grid_preferences', 'admin_grid_bookmarks']);
});
