<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAggregatorCandidateConsumer;

it('consumes the isolated page momentum runtime candidate without live mutation', function (): void {
    $root = dirname(__DIR__, 5);
    $candidate = require $root . '/app/zoosper-page/config/admin_page_momentum_runtime_candidate.php';
    $consumed = (new PageMomentumAggregatorCandidateConsumer())->consume($candidate);

    expect($consumed['enabled'])->toBeTrue();
    expect($consumed['routeCount'])->toBe(1);
    expect($consumed['menuCount'])->toBe(1);
    expect($consumed['routes'][0]['name'])->toBe('admin.page_momentum.index');
    expect($consumed['menuItems'][0]['route'])->toBe('admin.page_momentum.index');
    expect($consumed['liveMutation'])->toBeFalse();
    expect($consumed['rollback'])->toBeArray();
});

it('keeps phase 1.50 closure tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-page-admin-momentum-candidate-consumer.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-phase-150-closure.php')->toBeFile();
});
