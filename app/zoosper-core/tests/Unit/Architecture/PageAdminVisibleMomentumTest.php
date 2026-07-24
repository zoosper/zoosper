<?php

declare(strict_types=1);

it('keeps visible page admin momentum artefacts available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-page-admin-visible-momentum.php')->toBeFile();
    expect($root . '/tools/write-page-admin-visible-momentum-plan.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-visible-momentum-closure.php')->toBeFile();
    expect($root . '/app/zoosper-page/config/admin_page_momentum.php')->toBeFile();
    expect($root . '/app/zoosper-page/resources/views/admin/page-momentum.latte')->toBeFile();
});

it('keeps page momentum disabled until wiring phase', function (): void {
    $root = dirname(__DIR__, 5);
    $config = require $root . '/app/zoosper-page/config/admin_page_momentum.php';

    expect($config)->toBeArray();
    expect($config['page_momentum']['enabled'])->toBeFalse();
    expect($config['page_momentum']['items'])->toBeArray();
});
