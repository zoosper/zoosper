<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use ReflectionMethod;
use Zoosper\Page\Admin\PageGridExportCoordinator;

test('Pages export uses a fixed server-owned filename', function (): void {
    expect(PageGridExportCoordinator::FILENAME)->toBe('pages.csv');
});

test('Pages export requires a resolved Grid view state', function (): void {
    $parameters = (new ReflectionMethod(PageGridExportCoordinator::class, 'export'))
        ->getParameters();

    expect($parameters[0]->getName())->toBe('state');
    expect((string) $parameters[0]->getType())->toBe('Zoosper\\AdminGrid\\GridViewState');
});
