<?php

declare(strict_types=1);

use Zoosper\Admin\Asset\AdminAssetRegistry;
use Zoosper\Core\Module\ModuleRegistry;

/*
 * Phase B4 behavioural test for the confirmed real bug: two independently
 * authored modules (zoosper-admin and zoosper-page) both declared an admin
 * asset entry pointing at the SAME physical zoosper-tag-selector.css file
 * under different handles, so it was rendered as two duplicate <link> tags.
 * Reproduces the exact scenario with real temp module fixtures (mirroring the
 * technique used in Phase 1.108/B2's tests) rather than a synthetic shortcut.
 */

function removeAssetFixtureDir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = scandir($dir) ?: [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? removeAssetFixtureDir($path) : unlink($path);
    }
    rmdir($dir);
}

/**
 * Create a temp base path with two enabled modules, each declaring its own
 * config/admin_assets.php via the raw array shape AdminAssetRegistry expects.
 */
function makeDuplicateAssetFixture(): string
{
    $base = sys_get_temp_dir() . '/zoosper-asset-registry-test-' . bin2hex(random_bytes(8));

    $moduleA = $base . '/app/module-a';
    mkdir($moduleA . '/config', 0775, true);
    file_put_contents($moduleA . '/module.php', "<?php\ndeclare(strict_types=1);\nreturn ['name' => 'module-a', 'enabled' => true, 'sort_order' => 10];\n");
    file_put_contents(
        $moduleA . '/config/admin_assets.php',
        "<?php\ndeclare(strict_types=1);\nreturn ['assets' => ["
        . "'module-a-shared-style' => ['type' => 'style', 'path' => '/assets/admin/css/shared.css', 'sort_order' => 10],"
        . "]];\n",
    );

    $moduleB = $base . '/app/module-b';
    mkdir($moduleB . '/config', 0775, true);
    file_put_contents($moduleB . '/module.php', "<?php\ndeclare(strict_types=1);\nreturn ['name' => 'module-b', 'enabled' => true, 'sort_order' => 20];\n");
    file_put_contents(
        $moduleB . '/config/admin_assets.php',
        "<?php\ndeclare(strict_types=1);\nreturn ['assets' => ["
        // Same PHYSICAL path as module-a's asset, but a DIFFERENT handle and a
        // different cache-busting query string — exactly the real-world shape
        // of the confirmed bug (two modules, one physical file).
        . "'module-b-tag-selector-style' => ['type' => 'style', 'path' => '/assets/admin/css/shared.css?v=1.37l', 'sort_order' => 40],"
        . "]];\n",
    );

    return $base;
}

it('collapses two modules declaring the SAME physical asset into one <link>', function (): void {
    $base = makeDuplicateAssetFixture();

    try {
        $registry = new AdminAssetRegistry(new ModuleRegistry($base));
        $stylesheets = $registry->stylesheets();

        // Without the fix this would be 2 — the exact confirmed duplicate.
        expect($stylesheets)->toHaveCount(1)
            // The first occurrence in final sort order wins (module-a's entry,
            // sort_order 10, before module-b's sort_order 40).
            ->and($stylesheets[0]->handle)->toBe('module-a-shared-style');
    } finally {
        removeAssetFixtureDir($base);
    }
});

it('does NOT collapse genuinely different physical assets', function (): void {
    $base = sys_get_temp_dir() . '/zoosper-asset-registry-distinct-' . bin2hex(random_bytes(8));
    $moduleDir = $base . '/app/module-c';
    mkdir($moduleDir . '/config', 0775, true);
    file_put_contents($moduleDir . '/module.php', "<?php\ndeclare(strict_types=1);\nreturn ['name' => 'module-c', 'enabled' => true, 'sort_order' => 10];\n");
    file_put_contents(
        $moduleDir . '/config/admin_assets.php',
        "<?php\ndeclare(strict_types=1);\nreturn ['assets' => ["
        . "'style-one' => ['type' => 'style', 'path' => '/assets/admin/css/one.css', 'sort_order' => 10],"
        . "'style-two' => ['type' => 'style', 'path' => '/assets/admin/css/two.css', 'sort_order' => 20],"
        . "]];\n",
    );

    try {
        $registry = new AdminAssetRegistry(new ModuleRegistry($base));

        expect($registry->stylesheets())->toHaveCount(2);
    } finally {
        removeAssetFixtureDir($base);
    }
});

it('treats the same path with different cache-busting query strings as ONE asset', function (): void {
    $base = sys_get_temp_dir() . '/zoosper-asset-registry-querystring-' . bin2hex(random_bytes(8));
    $moduleDir = $base . '/app/module-d';
    mkdir($moduleDir . '/config', 0775, true);
    file_put_contents($moduleDir . '/module.php', "<?php\ndeclare(strict_types=1);\nreturn ['name' => 'module-d', 'enabled' => true, 'sort_order' => 10];\n");
    file_put_contents(
        $moduleDir . '/config/admin_assets.php',
        "<?php\ndeclare(strict_types=1);\nreturn ['assets' => ["
        . "'v1' => ['type' => 'style', 'path' => '/assets/admin/css/versioned.css?v=1', 'sort_order' => 10],"
        . "'v2' => ['type' => 'style', 'path' => '/assets/admin/css/versioned.css?v=2', 'sort_order' => 20],"
        . "]];\n",
    );

    try {
        $registry = new AdminAssetRegistry(new ModuleRegistry($base));
        $stylesheets = $registry->stylesheets();

        expect($stylesheets)->toHaveCount(1)
            ->and($stylesheets[0]->handle)->toBe('v1');
    } finally {
        removeAssetFixtureDir($base);
    }
});
