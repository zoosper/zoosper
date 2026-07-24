<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAggregatorPatchDraft;

it('builds a page momentum aggregator patch draft without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $draft = (new PageMomentumAggregatorPatchDraft())->draft(
        [
            'readyForPatchDraft' => true,
        ],
        require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php',
        require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php',
    );

    expect($draft['readyForPatchDraft'])->toBeTrue();
    expect($draft['routeName'])->toBe('admin.page_momentum.index');
    expect($draft['menuRoute'])->toBe('admin.page_momentum.index');
    expect($draft['liveMutation'])->toBeFalse();
    expect($draft['rollback'])->toBeArray();
});

it('keeps phase 1.49 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/generate-page-admin-momentum-aggregator-patch-draft.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-149-closure.php')->toBeFile();
});
