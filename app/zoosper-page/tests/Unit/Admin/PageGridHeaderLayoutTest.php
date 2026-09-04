<?php

declare(strict_types=1);

it('renders Page title and actions in one explicit heading row', function (): void {
    $root = dirname(__DIR__, 5);

    $view = (string) file_get_contents(
        $root . '/app/zoosper-page/resources/views/admin/pages/index.php',
    );

    $css = (string) file_get_contents(
        $root . '/app/zoosper-page/resources/admin/css/page-grid-workspace.css',
    );

    expect($view)
        ->toContain('page-grid-index__heading-row')
        ->toContain('page-grid-index__heading')
        ->toContain('page-grid-index__title')
        ->toContain('page-grid-index__actions')
        ->toContain('href="<?= $escape($exportUrl) ?>">Export</a>')
        ->toContain('href="<?= $escape($createUrl) ?>">Create page</a>')
        ->and($css)
        ->toContain('Phase 12B-C3.6: final structural Page action alignment.')
        ->toContain('.page-grid-index__heading-row')
        ->toContain('align-items: start;')
        ->toContain('justify-content: space-between;')
        ->toContain('transform: none !important;');
});

it('uses the server-owned Pages shell-title policy without a browser DOM scan', function (): void {
    $root = dirname(__DIR__, 5);
    $responder = (string) file_get_contents(
        $root . '/app/zoosper-page/src/Admin/PageAdminGridResponder.php',
    );
    $script = (string) file_get_contents(
        $root . '/app/zoosper-page/resources/admin/js/page-grid-search.js',
    );

    expect($responder)
        ->toContain("shellTitle: ''")
        ->and($script)
        ->not->toContain("candidate.textContent?.trim() !== 'Pages'")
        ->not->toContain('candidate.hidden = true')
        ->not->toContain('pageGridDuplicateShellTitle')
        ->toContain("document.querySelectorAll('.page-grid-index').forEach(initialise)");
});
