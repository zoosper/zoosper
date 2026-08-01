<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use ReflectionMethod;
use Zoosper\Page\Admin\PageGridCompletePageBuilder;

test('complete Pages builder requires authenticated identity CSRF and pagination', function (): void {
    $parameters = (new ReflectionMethod(PageGridCompletePageBuilder::class, 'build'))
        ->getParameters();

    expect($parameters[0]->getName())->toBe('authenticatedAdminUserId');
    expect((string) $parameters[0]->getType())->toBe('int');
    expect((string) $parameters[2]->getType())->toBe('Zoosper\\AdminGrid\\GridWorkspaceCsrf');
    expect((string) $parameters[3]->getType())->toBe('Zoosper\\AdminGrid\\GridWorkspacePagination');
});

test('complete Pages builder reuses state returned by the page builder', function (): void {
    $source = (string) file_get_contents(
        dirname(__DIR__, 5) . '/app/zoosper-page/src/Admin/PageGridCompletePageBuilder.php',
    );

    expect($source)->toContain('$page->state')
        ->not->toContain('$_GET')
        ->not->toContain('$_POST')
        ->not->toContain('new GridViewState');
});
