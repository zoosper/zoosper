<?php

declare(strict_types=1);

use Zoosper\Core\Http\Response;
use Zoosper\Page\Admin\Controller\PageMomentumAdminHttpController;
use Zoosper\Page\Admin\PageMomentumAdminResponseFactory;

it('adapts the page momentum string renderer to a core response object', function (): void {
    $response = (new PageMomentumAdminHttpController())->index();

    expect($response)->toBeInstanceOf(Response::class);
});

it('keeps response runtime fix tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/fix-page-admin-momentum-response-controller.php')->toBeFile();
    expect($root . '/tools/audit-page-admin-momentum-response-runtime.php')->toBeFile();
});

it('keeps the response factory available', function (): void {
    expect(class_exists(PageMomentumAdminResponseFactory::class))->toBeTrue();
});
