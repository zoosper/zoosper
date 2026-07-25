<?php

declare(strict_types=1);

use Zoosper\Core\Http\Response;
use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
use Zoosper\Page\Admin\Controller\PageMomentumAdminHttpController;

it('keeps the live page momentum HTTP controller available', function (): void {
    expect(class_exists(PageMomentumAdminHttpController::class))->toBeTrue();
});

it('adapts the rendered page momentum dashboard to a core response object', function (): void {
    $response = (new PageMomentumAdminHttpController())->index();

    expect($response)->toBeInstanceOf(Response::class);
});

it('keeps the durable page momentum dashboard output available', function (): void {
    $html = (new PageMomentumAdminController())->index();

    expect($html)->toContain('/admin/page-momentum');
    expect($html)->toContain('read-only');
    expect($html)->toContain('Page momentum');
});
