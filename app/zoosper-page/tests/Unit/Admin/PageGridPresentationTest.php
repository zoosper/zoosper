<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use ReflectionMethod;
use Zoosper\Page\Admin\PageGridPresentation;

test('Page presentation requires host-provided CSRF field and token', function (): void {
    $parameters = (new ReflectionMethod(PageGridPresentation::class, 'render'))->getParameters();

    expect($parameters[2]->getName())->toBe('csrfField');
    expect((string) $parameters[2]->getType())->toBe('string');
    expect($parameters[3]->getName())->toBe('csrfToken');
    expect((string) $parameters[3]->getType())->toBe('string');
});










