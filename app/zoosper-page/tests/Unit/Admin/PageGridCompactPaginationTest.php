<?php

declare(strict_types=1);

it('compacts existing Page pagination into one responsive footer row', function (): void {
    $root = dirname(__DIR__, 5);
    $css = (string) file_get_contents(
        $root . '/app/zoosper-page/resources/admin/css/page-grid-workspace.css',
    );
    $script = (string) file_get_contents(
        $root . '/app/zoosper-page/resources/admin/js/page-grid-search.js',
    );
    $assets = require $root . '/app/zoosper-page/config/admin_assets.php';
    $version = substr(
        hash('sha256', (string) preg_replace('~\r\n?~', "\n", $css)),
        0,
        12,
    );

    expect($css)
        ->toContain('Phase 12B-C3.1: compact the existing Page pagination nodes')
        ->toContain('grid-template-columns: minmax(6rem, 1fr) auto minmax(6rem, 1fr);')
        ->toContain('.page-grid-index__pagination > :first-child')
        ->toContain('.page-grid-index__pagination > :last-child')
        ->toContain('.page-grid-index__pagination > .grid-pagination-controls')
        ->toContain('grid-column: 1 / -1;')
        ->toContain('@media (max-width: 30rem)')
        ->toContain('[data-page-grid-pagination]')
        ->toContain('grid-template-columns: minmax(7rem, 1fr) auto minmax(7rem, 1fr);')
        ->and($script)
        ->toContain("page.querySelector('.grid-pagination-controls')")
        ->toContain("footer.dataset.pageGridPagination = ''")
        ->toContain("table.insertAdjacentElement('afterend', footer)")
        ->toContain('footer.append(paginationControls)')
        ->toContain("disabledPrevious.textContent = '« Previous'")
        ->toContain("disabledNext.textContent = 'Next »'")
        ->toContain('navigation.remove()')
        ->toContain('Node.DOCUMENT_POSITION_FOLLOWING')
        ->toContain('const paginationOnly =')
        ->toContain('containsFunctionalContent')
        ->toContain('candidate.remove()')
        ->not->toContain('!navigation.children.length')
        ->not->toContain('cloneNode')
        ->and($assets['assets']['zoosper-page-grid-workspace-style']['path'] ?? null)
        ->toBe('/asset/zoosper-page/css/page-grid-workspace.css?v=' . $version);
});

it('keeps Page pagination refinement outside the shared Grid package', function (): void {
    $root = dirname(__DIR__, 5);
    $shared = (string) file_get_contents(
        $root . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css',
    );

    expect($shared)->not->toContain('page-grid-index__pagination');
});
