<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\PageAdminDashboardStatusPresenter;

it('maps dashboard statuses to visual css classes', function (): void {
    $presenter = new PageAdminDashboardStatusPresenter();

    expect($presenter->classFor('ready'))->toBe('zsp-status zsp-status--ready');
    expect($presenter->classFor('active'))->toBe('zsp-status zsp-status--active');
    expect($presenter->classFor('track'))->toBe('zsp-status zsp-status--track');
    expect($presenter->classFor('planned'))->toBe('zsp-status zsp-status--planned');
    expect($presenter->classFor('documented'))->toBe('zsp-status zsp-status--documented');
    expect($presenter->classFor('in-progress'))->toBe('zsp-status zsp-status--in-progress');
});

it('renders visual status badges on the dashboard', function (): void {
    $html = (new PageMomentumAdminController())->index();

    expect($html)->toContain('zsp-status--ready');
    expect($html)->toContain('zsp-status--active');
    expect($html)->toContain('zsp-status--track');
    expect($html)->toContain('zsp-status--planned');
    expect($html)->toContain('zsp-status--documented');
    expect($html)->toContain('zsp-status--in-progress');
});

it('keeps phase 1.61 status system tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/smoke-page-admin-dashboard-status-system.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-dashboard-status-system.php')->toBeFile();
});
