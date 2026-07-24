<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAggregatorPatchBuilder;

it('builds an isolated page momentum runtime candidate without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $candidate = (new PageMomentumAggregatorPatchBuilder())->buildCandidate(
        require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php',
        require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php',
    );

    $integration = $candidate['page_momentum_admin_integration'];

    expect($integration['enabled'])->toBeTrue();
    expect($integration['routes'])->toBeArray()->toHaveCount(1);
    expect($integration['menu_items'])->toBeArray()->toHaveCount(1);
    expect($integration['live_mutation'])->toBeFalse();
});

it('keeps phase 1.50 candidate tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/apply-page-admin-momentum-aggregator-candidate.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-aggregator-candidate.php')->toBeFile();
});
