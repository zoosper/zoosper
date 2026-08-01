<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use ReflectionMethod;
use Zoosper\Page\Admin\PageGridHttpCoordinator;

test('Pages HTTP coordinator requires authenticated admin identity for both paths', function (): void {
    foreach (['view', 'mutate'] as $methodName) {
        $parameters = (new ReflectionMethod(PageGridHttpCoordinator::class, $methodName))->getParameters();
        expect($parameters[0]->getName())->toBe('adminUserId');
        expect((string) $parameters[0]->getType())->toBe('int');
    }
});

test('Pages coordinator source does not accept client-owned grid or user identity', function (): void {
    $source = (string) file_get_contents(
        dirname(__DIR__, 5) . '/app/zoosper-page/src/Admin/PageGridHttpCoordinator.php',
    );

    expect($source)->not->toContain("['admin_user_id']")
        ->not->toContain("['grid_key']")
        ->not->toContain("['redirect']");
});
