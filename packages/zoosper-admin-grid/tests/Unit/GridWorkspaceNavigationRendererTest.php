<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridWorkspaceNavigation;
use Zoosper\AdminGrid\GridWorkspaceNavigationRenderer;

test('navigation renders previous next and export as real links', function (): void {
    $html = (new GridWorkspaceNavigationRenderer())->render(
        new GridWorkspaceNavigation(
            previousUrl: '/admin/pages?page=1',
            nextUrl: '/admin/pages?page=3',
            sortUrls: [],
            exportUrl: '/admin/pages/export?status=published',
        ),
    );

    expect($html)->toContain('rel="prev"')
        ->toContain('rel="next"')
        ->toContain('data-grid-export')
        ->toContain('href="/admin/pages/export?status=published"');
});

test('navigation omits unavailable page links and rejects external URLs', function (): void {
    $html = (new GridWorkspaceNavigationRenderer())->render(
        new GridWorkspaceNavigation(null, null, [], '/admin/pages/export'),
    );

    expect($html)->not->toContain('rel="prev"')
        ->not->toContain('rel="next"');
    expect(fn () => new GridWorkspaceNavigation(
        null,
        null,
        [],
        'https://example.invalid',
    ))->toThrow(\InvalidArgumentException::class, 'application-local');
});
