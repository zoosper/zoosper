<?php

declare(strict_types=1);

it('retires superseded Page Grid integration scaffolding', function (): void {
    $root = dirname(__DIR__, 5);
    foreach ([
        'app/zoosper-page/src/Admin/PageGridCompletePageBuilder.php',
        'app/zoosper-page/src/Admin/PageGridControllerAdapter.php',
        'app/zoosper-page/src/Admin/PageGridControllerContract.php',
    ] as $relative) {
        expect(file_exists($root . '/' . $relative), $relative)->toBeFalse();
    }
});

it('keeps the deployed Page Grid path explicit', function (): void {
    $root = dirname(__DIR__, 5);
    $factory = (string) file_get_contents($root . '/app/zoosper-page/config/services.php');
    $responder = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/PageAdminGridResponder.php');

    expect($factory)->toContain('PageAdminGridResponder::class => static function')
        ->toContain('new PageGridWorkspace(')
        ->toContain('new PageGridMutationCoordinator(')
        ->and($responder)->toContain('PageGridWorkspace')
        ->toContain('PageGridMutationCoordinator');
});










