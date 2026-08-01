<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use ReflectionMethod;
use Zoosper\Page\Admin\PageGridEndpointContract;
use Zoosper\Page\Admin\PageGridPageBuilder;

test('Pages Grid endpoints separate read and mutation methods', function (): void {
    expect(PageGridEndpointContract::VIEW_METHOD)->toBe('GET');
    expect(PageGridEndpointContract::VIEW_PATH)->toBe('/admin/pages');
    expect(PageGridEndpointContract::MUTATION_METHOD)->toBe('POST');
    expect(PageGridEndpointContract::MUTATION_PATH)->toBe('/admin/pages/grid');
    expect(PageGridPageBuilder::MUTATION_PATH)->toBe('/admin/pages/grid');
});

test('complete page build requires authenticated identity and CSRF value object', function (): void {
    $parameters = (new ReflectionMethod(PageGridPageBuilder::class, 'build'))->getParameters();

    expect($parameters[0]->getName())->toBe('authenticatedAdminUserId');
    expect((string) $parameters[0]->getType())->toBe('int');
    expect($parameters[2]->getName())->toBe('csrf');
    expect((string) $parameters[2]->getType())->toBe('Zoosper\\AdminGrid\\GridWorkspaceCsrf');
});
