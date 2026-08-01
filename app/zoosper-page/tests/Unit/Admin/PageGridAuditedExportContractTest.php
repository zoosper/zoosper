<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use ReflectionMethod;
use Zoosper\Page\Admin\PageGridAuditedExportCoordinator;
use Zoosper\Page\Admin\PageGridWorkspace;

test('audited Pages export requires authenticated admin identity', function (): void {
    $parameters = (new ReflectionMethod(PageGridAuditedExportCoordinator::class, 'export'))
        ->getParameters();

    expect($parameters[0]->getName())->toBe('authenticatedAdminUserId');
    expect((string) $parameters[0]->getType())->toBe('int');
    expect(PageGridWorkspace::GRID_KEY)->toBe('admin.pages');
});
