<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use ReflectionClass;
use Zoosper\AdminGrid\GridViewStateResolver;

test('view-state resolver requires explicit admin and grid identity', function (): void {
    $method = (new ReflectionClass(GridViewStateResolver::class))->getMethod('resolve');
    $parameters = $method->getParameters();

    expect($parameters[0]->getName())->toBe('adminUserId');
    expect((string) $parameters[0]->getType())->toBe('int');
    expect($parameters[1]->getName())->toBe('gridKey');
    expect((string) $parameters[1]->getType())->toBe('string');
});











