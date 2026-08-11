<?php

declare(strict_types=1);

it('adopts navigation and breadcrumbs in the live default frontend layout', function (): void {
    $root = dirname(__DIR__, 5);
    $layoutPath = $root . '/themes/default/templates/layout.latte';
    $pageViewPath = $root . '/app/zoosper-page/resources/views/page/view.latte';

    expect($layoutPath)->toBeFile();

    $layout = (string) file_get_contents($layoutPath);
    $pageView = (string) file_get_contents($pageViewPath);

    expect($layout)
        ->toContain('{$navigationHtml|noescape}')
        ->toContain('{$breadcrumbsHtml|noescape}')
        ->toContain('aria-label="Primary navigation"')
        ->toContain('aria-label="Breadcrumb"')
        ->toContain('class="site-navigation cms-site-navigation"')
        ->and($pageView)
        ->not->toContain('{$navigationHtml|noescape}')
        ->not->toContain('{$breadcrumbsHtml|noescape}');

    $headerStart = strpos($layout, '<header');
    $headerEnd = strpos($layout, '</header>');
    $navigation = strpos($layout, '{$navigationHtml|noescape}');
    expect($headerStart)->not->toBeFalse()
        ->and($headerEnd)->not->toBeFalse()
        ->and($navigation)->toBeGreaterThan($headerStart)
        ->and($navigation)->toBeLessThan($headerEnd);

    $mainStart = strpos($layout, '<main');
    $breadcrumbs = strpos($layout, '{$breadcrumbsHtml|noescape}');
    expect($mainStart)->not->toBeFalse()
        ->and($breadcrumbs)->toBeGreaterThan($mainStart);
});
