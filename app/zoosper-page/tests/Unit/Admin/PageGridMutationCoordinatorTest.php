<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use ReflectionMethod;
use Zoosper\Page\Admin\PageGridControllerContract;
use Zoosper\Page\Admin\PageGridMutationCoordinator;

test('Page mutation coordinator requires authenticated server-owned user identity', function (): void {
    $parameters = (new ReflectionMethod(PageGridMutationCoordinator::class, 'mutate'))
        ->getParameters();

    expect($parameters[0]->getName())->toBe('authenticatedAdminUserId');
    expect((string) $parameters[0]->getType())->toBe('int');
});

test('Page controller contract separates view and mutation paths', function (): void {
    $view = new ReflectionMethod(PageGridControllerContract::class, 'viewGrid');
    $mutate = new ReflectionMethod(PageGridControllerContract::class, 'mutateGrid');

    expect($view->isPublic())->toBeTrue();
    expect($mutate->isPublic())->toBeTrue();
    expect((string) $mutate->getReturnType())
        ->toBe('Zoosper\\AdminGrid\\GridWorkspaceMutationResult');
});
