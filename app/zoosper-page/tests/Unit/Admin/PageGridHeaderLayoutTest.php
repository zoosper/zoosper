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

it('removes only the duplicate Pages shell label', function (): void {
    $root = dirname(__DIR__, 5);

    $script = (string) file_get_contents(
        $root . '/app/zoosper-page/resources/admin/js/page-grid-search.js',
    );

    expect($script)
        ->toContain("candidate.textContent?.trim() !== 'Pages'")
        ->toContain("candidate.closest('.page-grid-index')")
        ->toContain("candidate.closest('aside')")
        ->toContain("candidate.closest('nav')")
        ->toContain('bounds.top < 80')
        ->toContain('candidate.hidden = true')
        ->toContain('pageGridDuplicateShellTitle');
});
