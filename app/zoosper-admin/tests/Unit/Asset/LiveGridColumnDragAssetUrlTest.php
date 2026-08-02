<?php

declare(strict_types=1);

it('uses module asset URLs backed by resources assets files', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/app/zoosper-admin/config/admin_assets.php';
    $source = var_export($manifest, true);

    expect($source)->toContain('/asset/zoosper-admin/css/zoosper-grid-column-drag.css')
        ->toContain('/asset/zoosper-admin/js/zoosper-grid-column-drag.js')
        ->and($root . '/app/zoosper-admin/resources/assets/css/zoosper-grid-column-drag.css')->toBeFile()
        ->and($root . '/app/zoosper-admin/resources/assets/js/zoosper-grid-column-drag.js')->toBeFile();
});
