<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use ReflectionMethod;
use Zoosper\Page\Admin\PageGridMutationHandler;

test('Pages mutation handler requires explicit authenticated admin identity', function (): void {
    $method = new ReflectionMethod(PageGridMutationHandler::class, 'handle');
    $parameters = $method->getParameters();

    expect($parameters[0]->getName())->toBe('adminUserId');
    expect((string) $parameters[0]->getType())->toBe('int');
    expect($parameters[1]->getName())->toBe('action');
    expect((string) $parameters[1]->getType())->toBe('string');
});










