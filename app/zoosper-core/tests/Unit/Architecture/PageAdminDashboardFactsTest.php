<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageAdminDashboardFactProvider;

it('provides read-only dashboard facts', function (): void {
    $facts = (new PageAdminDashboardFactProvider())->facts();

    expect($facts)->toHaveCount(4);
    expect(array_column($facts, 'label'))->toContain('Live route fact');
    expect(array_column($facts, 'label'))->toContain('Live menu fact');
    expect(array_column($facts, 'label'))->toContain('Renderer controller fact');
    expect(array_column($facts, 'label'))->toContain('HTTP controller fact');
});

it('renders real dashboard facts on the momentum panel', function (): void {
    $html = (new PageMomentumAdminController())->index();

    expect($html)->toContain('Real dashboard facts');
    expect($html)->toContain('Live route fact');
    expect($html)->toContain('Live menu fact');
    expect($html)->toContain('Renderer controller fact');
    expect($html)->toContain('HTTP controller fact');
});

it('keeps phase 1.62 dashboard fact tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/smoke-page-admin-dashboard-facts.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-dashboard-facts.php')->toBeFile();
});
