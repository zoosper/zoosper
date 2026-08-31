<?php

declare(strict_types=1);

it('passes computed navigation and breadcrumbs into the shared page and layout view data', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Service/PageRenderer.php');

    $dataStart = strpos($source, '$data = [');
    $navigationData = strpos($source, "'navigationHtml' => \$navigation['navigationHtml']", $dataStart);
    $breadcrumbData = strpos($source, "'breadcrumbsHtml' => \$navigation['breadcrumbsHtml']", $dataStart);
    $bodyRender = strpos($source, "renderToString('zoosper-page::page/view'", $dataStart);
    $layoutRender = strpos($source, 'renderLayout(', $bodyRender);

    expect($dataStart)->not->toBeFalse()
        ->and($navigationData)->not->toBeFalse()
        ->and($breadcrumbData)->not->toBeFalse()
        ->and($bodyRender)->not->toBeFalse()
        ->and($layoutRender)->not->toBeFalse()
        ->and($navigationData)->toBeGreaterThan($dataStart)
        ->and($breadcrumbData)->toBeGreaterThan($navigationData)
        ->and($bodyRender)->toBeGreaterThan($breadcrumbData)
        ->and($layoutRender)->toBeGreaterThan($bodyRender);
});

it('keeps the default theme conditional so an absent menu emits no empty landmark', function (): void {
    $root = dirname(__DIR__, 5);
    $layout = (string) file_get_contents($root . '/themes/default/templates/layout.latte');

    expect($layout)
        ->toContain("{if (\$navigationHtml ?? '') !== ''}")
        ->toContain('{$navigationHtml|noescape}')
        ->toContain("{if (\$breadcrumbsHtml ?? '') !== ''}")
        ->toContain('{$breadcrumbsHtml|noescape}');
});










