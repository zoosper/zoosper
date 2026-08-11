<?php

declare(strict_types=1);

it('keeps PageRenderer optionally extensible through a Page-owned navigation contract', function (): void {
    $root = dirname(__DIR__, 3);
    $renderer = (string) file_get_contents($root . '/src/Service/PageRenderer.php');
    $template = (string) file_get_contents($root . '/resources/views/page/view.latte');

    expect($renderer)
        ->toContain('FrontendNavigationContributorInterface')
        ->toContain("'navigationHtml'")
        ->toContain("'breadcrumbsHtml'")
        ->and($template)->not->toContain('{$navigationHtml|noescape}')->not->toContain('{$breadcrumbsHtml|noescape}');
});
