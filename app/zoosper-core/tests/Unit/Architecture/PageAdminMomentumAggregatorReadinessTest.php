<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAggregatorIntegrationPlan;

it('builds page momentum aggregator integration plan without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $planner = new PageMomentumAggregatorIntegrationPlan();

    $plan = $planner->build(
        require $root . '/app/zoosper-page/config/admin_page_momentum_routes.php',
        require $root . '/app/zoosper-page/config/admin_page_momentum_menu.php',
        [
            'routeFiles' => ['app/zoosper-page/config/controllers.php'],
            'menuFiles' => ['app/zoosper-page/config/admin_menu.php'],
            'controllerFiles' => ['app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php'],
        ],
    );

    expect($plan['routeMetadataEnabled'])->toBeTrue();
    expect($plan['menuMetadataEnabled'])->toBeTrue();
    expect($plan['routeCount'])->toBe(1);
    expect($plan['menuCount'])->toBe(1);
    expect($plan['readyForPatchDraft'])->toBeTrue();
    expect($plan['liveMutation'])->toBeFalse();
});

it('keeps page momentum aggregator readiness tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/discover-admin-route-menu-aggregators.php')->toBeFile();
    expect($root . '/tools/generate-page-admin-momentum-aggregator-integration-plan.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-aggregator-readiness.php')->toBeFile();
});
