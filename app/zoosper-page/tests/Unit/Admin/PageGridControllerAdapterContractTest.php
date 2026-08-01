<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use ReflectionMethod;
use Zoosper\Page\Admin\PageGridControllerAdapter;
use Zoosper\Page\Admin\PageGridResponse;

test('controller adapter has explicit index mutation and export paths', function (): void {
    foreach (['index', 'mutate', 'export'] as $method) {
        $reflection = new ReflectionMethod(PageGridControllerAdapter::class, $method);
        expect($reflection->isPublic())->toBeTrue();
        expect((string) $reflection->getReturnType())->toBe(PageGridResponse::class);
        expect($reflection->getParameters()[0]->getName())
            ->toBe('authenticatedAdminUserId');
    }
});

test('response redirect rejects external locations', function (): void {
    expect(PageGridResponse::redirect('/admin/pages')->status)->toBe(303);
    expect(fn () => PageGridResponse::redirect('https://example.invalid'))
        ->toThrow(\InvalidArgumentException::class, 'local application path');
});
